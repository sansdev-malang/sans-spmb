<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbUnit;
use App\Models\SpmbWave;
use App\Models\SpmbGrade;
use App\Models\SpmbType;
use App\Models\SpmbClassProgram;
use App\Models\Setting;
use App\Services\SimpleXlsxService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class AdminReportAnalyticsController extends Controller
{
    /**
     * Prepare complete dataset for Registration Analytics & Funnel.
     */
    protected function prepareRegistrationData(Request $request): array
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $units = SpmbUnit::orderBy('id', 'asc')->get();
        if (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $units = $units->where('id', auth()->user()->spmb_unit_id);
        }

        $waves = SpmbWave::orderBy('id', 'asc')->get();
        $types = SpmbType::orderBy('id', 'asc')->get();
        $classPrograms = SpmbClassProgram::orderBy('id', 'asc')->get();
        $selectedPeriod = SpmbPeriod::find($selectedPeriodId);

        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'user'])
            ->where('spmb_period_id', $selectedPeriodId);

        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }

        if ($request->filled('type_id')) {
            $query->where('spmb_type_id', $request->type_id);
        }

        if ($request->filled('class_program_id')) {
            $query->where('spmb_class_program_id', $request->class_program_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $candidates = $query->get();

        // 1. Funnel Pipeline Counts
        $totalRegistered = $candidates->count();
        $totalDraft = $candidates->where('registration_status', 'draft')->count();
        $totalSubmitted = $candidates->where('registration_status', 'submitted')->count();
        $totalVerified = $candidates->whereIn('registration_status', ['verified', 'taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed'])->count();
        $totalTaarufScheduled = $candidates->where('registration_status', 'taaruf_scheduled')->count();
        $totalTaarufCompleted = $candidates->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed'])->count();
        $totalAgreementSigned = $candidates->whereIn('registration_status', ['agreement_signed', 'completed'])->count();
        $totalCompleted = $candidates->where('registration_status', 'completed')->count();
        $totalRejected = $candidates->whereIn('registration_status', ['rejected', 'failed'])->count();

        // In-progress bottleneck metrics
        $pendingVerificationCount = $totalSubmitted;
        $pendingTaarufScheduleCount = $candidates->where('registration_status', 'verified')->whereNull('observation_date')->count();
        $pendingTaarufObservationCount = $totalTaarufScheduled;
        $pendingDspPaymentCount = $candidates->whereIn('registration_status', ['taaruf_completed', 'agreement_signed'])->count();

        // Conversion Rates
        $rateDraftToSubmitted = $totalRegistered > 0 ? round((($totalRegistered - $totalDraft) / $totalRegistered) * 100, 1) : 0;
        $rateSubmittedToVerified = ($totalRegistered - $totalDraft) > 0 ? round(($totalVerified / ($totalRegistered - $totalDraft)) * 100, 1) : 0;
        $rateVerifiedToTaaruf = $totalVerified > 0 ? round(($totalTaarufCompleted / $totalVerified) * 100, 1) : 0;
        $rateTaarufToCompleted = $totalTaarufCompleted > 0 ? round(($totalCompleted / $totalTaarufCompleted) * 100, 1) : 0;
        $overallConversionRate = $totalRegistered > 0 ? round(($totalCompleted / $totalRegistered) * 100, 1) : 0;

        // 2. Gender Distribution (with 100% sum guarantee)
        $maleCount = $candidates->filter(fn($c) => in_array(strtolower($c->gender ?? ''), ['l', 'laki-laki', 'male']))->count();
        $femaleCount = $candidates->filter(fn($c) => in_array(strtolower($c->gender ?? ''), ['p', 'perempuan', 'female']))->count();
        $unknownGenderCount = $totalRegistered - ($maleCount + $femaleCount);

        // 3. Jalur Masuk (SpmbType) Breakdown
        $typeStats = [];
        foreach ($types as $t) {
            $tCands = $candidates->where('spmb_type_id', $t->id);
            $typeStats[] = [
                'type' => $t,
                'registered' => $tCands->count(),
                'verified' => $tCands->whereIn('registration_status', ['verified', 'taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed'])->count(),
                'completed' => $tCands->where('registration_status', 'completed')->count(),
                'percentage' => $totalRegistered > 0 ? round(($tCands->count() / $totalRegistered) * 100, 1) : 0,
            ];
        }

        // 4. Program Kelas (SpmbClassProgram) Breakdown
        $classProgramStats = [];
        foreach ($classPrograms as $cp) {
            $cpCands = $candidates->where('spmb_class_program_id', $cp->id);
            $classProgramStats[] = [
                'program' => $cp,
                'registered' => $cpCands->count(),
                'verified' => $cpCands->whereIn('registration_status', ['verified', 'taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed'])->count(),
                'completed' => $cpCands->where('registration_status', 'completed')->count(),
                'percentage' => $totalRegistered > 0 ? round(($cpCands->count() / $totalRegistered) * 100, 1) : 0,
            ];
        }

        // 5. Wave Breakdown
        $waveStats = [];
        foreach ($waves as $w) {
            $wCands = $candidates->where('spmb_wave_id', $w->id);
            $waveStats[] = [
                'wave' => $w,
                'registered' => $wCands->count(),
                'verified' => $wCands->whereIn('registration_status', ['verified', 'taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed'])->count(),
                'completed' => $wCands->where('registration_status', 'completed')->count(),
                'percentage' => $totalRegistered > 0 ? round(($wCands->count() / $totalRegistered) * 100, 1) : 0,
            ];
        }

        // 6. Unit & Grade Hierarchical Matrix
        $unitStats = [];
        foreach ($units as $u) {
            $uCands = $candidates->where('spmb_unit_id', $u->id);
            $uRegistered = $uCands->count();
            $uDraft = $uCands->where('registration_status', 'draft')->count();
            $uSubmitted = $uCands->where('registration_status', 'submitted')->count();
            $uVerified = $uCands->whereIn('registration_status', ['verified', 'taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed'])->count();
            $uTaaruf = $uCands->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed'])->count();
            $uCompleted = $uCands->where('registration_status', 'completed')->count();
            $uPercentage = $uRegistered > 0 ? round(($uCompleted / $uRegistered) * 100, 1) : 0;
            
            // Grades in this unit
            $grades = SpmbGrade::where('spmb_unit_id', $u->id)->where('is_active', true)->orderBy('id', 'asc')->get();
            $gradeStats = [];
            foreach ($grades as $g) {
                $gCands = $uCands->where('spmb_grade_id', $g->id);
                $gRegistered = $gCands->count();
                $gVerified = $gCands->whereIn('registration_status', ['verified', 'taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed'])->count();
                $gTaaruf = $gCands->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed'])->count();
                $gCompleted = $gCands->where('registration_status', 'completed')->count();
                $gPercentage = $gRegistered > 0 ? round(($gCompleted / $gRegistered) * 100, 1) : 0;
                
                $gradeStats[] = [
                    'grade' => $g,
                    'registered' => $gRegistered,
                    'verified' => $gVerified,
                    'taaruf' => $gTaaruf,
                    'completed' => $gCompleted,
                    'percentage' => $gPercentage,
                ];
            }

            $unitStats[] = [
                'unit' => $u,
                'registered' => $uRegistered,
                'draft' => $uDraft,
                'submitted' => $uSubmitted,
                'verified' => $uVerified,
                'taaruf' => $uTaaruf,
                'completed' => $uCompleted,
                'percentage' => $uPercentage,
                'grades' => $gradeStats,
            ];
        }

        $schoolName = Setting::get('school_name', 'Sekolah Islam Terpadu Anak Saleh');
        $schoolLogo = Setting::get('app_logo', 'assets/images/logo.png');
        $logoUrl = null;
        if ($schoolLogo && file_exists(public_path($schoolLogo))) {
            $logoUrl = public_path($schoolLogo);
        }

        return compact(
            'candidates',
            'totalRegistered',
            'totalDraft',
            'totalSubmitted',
            'totalVerified',
            'totalTaarufScheduled',
            'totalTaarufCompleted',
            'totalAgreementSigned',
            'totalCompleted',
            'totalRejected',
            'pendingVerificationCount',
            'pendingTaarufScheduleCount',
            'pendingTaarufObservationCount',
            'pendingDspPaymentCount',
            'rateDraftToSubmitted',
            'rateSubmittedToVerified',
            'rateVerifiedToTaaruf',
            'rateTaarufToCompleted',
            'overallConversionRate',
            'maleCount',
            'femaleCount',
            'unknownGenderCount',
            'typeStats',
            'classProgramStats',
            'waveStats',
            'unitStats',
            'units',
            'waves',
            'types',
            'classPrograms',
            'selectedPeriod',
            'schoolName',
            'logoUrl'
        );
    }

    /**
     * Display Registration Conversion Funnel & Class Quota Reports.
     */
    public function registrations(Request $request)
    {
        $data = $this->prepareRegistrationData($request);
        return view('admin.reports-registrations', $data);
    }

    /**
     * Export Registration Recap to Excel (.xlsx).
     */
    public function export(Request $request)
    {
        $data = $this->prepareRegistrationData($request);

        $unitCode = 'ALL';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        } elseif (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $u = SpmbUnit::find(auth()->user()->spmb_unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        }

        $period = $data['selectedPeriod'];
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';
        $filename = 'Rekapitulasi-Pendaftaran-Calon-Murid-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.xlsx';

        $columns = [
            'No.',
            'No. Registrasi',
            'Nama Calon Murid',
            'Unit Sekolah',
            'Tingkat / Kelas',
            'Gelombang',
            'Jalur Masuk',
            'Program Kelas',
            'Jenis Kelamin',
            'Nama Orang Tua / Wali',
            'No. WhatsApp Wali',
            'Tahapan Pendaftaran',
            'Waktu Pendaftaran',
        ];

        $rows = [];
        $i = 1;
        foreach ($data['candidates'] as $c) {
            $genderLabel = '-';
            if (in_array(strtolower($c->gender ?? ''), ['l', 'laki-laki', 'male'])) {
                $genderLabel = 'Laki-laki';
            } elseif (in_array(strtolower($c->gender ?? ''), ['p', 'perempuan', 'female'])) {
                $genderLabel = 'Perempuan';
            }

            $parentName = $c->guardian_name ?: ($c->father_name ?: ($c->mother_name ?: '-'));
            $parentPhone = $c->parent_phone ?: ($c->father_phone ?: ($c->mother_phone ?: '-'));

            $statusLabel = strtoupper(str_replace('_', ' ', $c->registration_status ?? '-'));

            $rows[] = [
                $i++,
                $c->id_label ?? '-',
                $c->candidate_name ?? '-',
                $c->unit->name ?? '-',
                $c->grade->name ?? ($c->admission_level ?? '-'),
                $c->wave->name ?? '-',
                $c->type->name ?? '-',
                $c->classProgram->name ?? 'Reguler',
                $genderLabel,
                $parentName,
                $parentPhone,
                $statusLabel,
                $c->created_at ? $c->created_at->translatedFormat('d M Y, H:i') : '-',
            ];
        }

        return SimpleXlsxService::download($columns, $rows, $filename, 'Rekap Pendaftar');
    }

    /**
     * Export Registration Recap to PDF (.pdf) A4 Landscape.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->prepareRegistrationData($request);

        $unitCode = 'ALL';
        $unitFilterLabel = 'Semua Jenjang / Unit';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) {
                $unitCode = strtoupper($u->code ?: Str::slug($u->name));
                $unitFilterLabel = $u->name;
            }
        } elseif (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $u = SpmbUnit::find(auth()->user()->spmb_unit_id);
            if ($u) {
                $unitCode = strtoupper($u->code ?: Str::slug($u->name));
                $unitFilterLabel = $u->name;
            }
        }

        $period = $data['selectedPeriod'];
        $periodName = $period ? ($period->name ?? $period->year) : 'SPMB';
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';
        $printedAt = now()->translatedFormat('d F Y, H:i') . ' WIB';
        $printedBy = auth()->user()->name ?? 'Administrator';

        $data['unitFilterLabel'] = $unitFilterLabel;
        $data['periodName'] = $periodName;
        $data['printedAt'] = $printedAt;
        $data['printedBy'] = $printedBy;
        $data['documentTitle'] = 'Laporan Rekapitulasi & Alur Konversi Pendaftaran Calon Murid';

        $filename = 'Laporan-Rekapitulasi-Pendaftaran-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.pdf';
        $pdf = Pdf::loadView('admin.reports-registrations-pdf', $data)->setPaper('a4', 'landscape');

        return response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Display Demographics, Origin Schools, and Marketing Source Reports.
     */
    public function demographics(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $units = SpmbUnit::orderBy('id', 'asc')->get();
        if (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $units = $units->where('id', auth()->user()->spmb_unit_id);
        }

        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade'])
            ->where('spmb_period_id', $selectedPeriodId);

        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        $candidates = $query->get();
        $totalCandidates = $candidates->count();

        // 1. Top Origin Schools (Sekolah Asal)
        $schoolCounts = [];
        $unfilledSchools = 0;
        foreach ($candidates as $c) {
            $prevSchool = trim($c->previous_school ?? '');
            if (!empty($prevSchool) && !in_array(strtolower($prevSchool), ['-', 'tidak ada', 'belum ada', 'belum sekolah', 'none'])) {
                $prevSchool = ucwords(strtolower($prevSchool));
                $schoolCounts[$prevSchool] = ($schoolCounts[$prevSchool] ?? 0) + 1;
            } else {
                $unfilledSchools++;
            }
        }
        arsort($schoolCounts);
        $topSchools = array_slice($schoolCounts, 0, 15, true);
        $totalFilledSchools = $totalCandidates - $unfilledSchools;

        // 2. City / Kabupaten Distribution
        $cityCounts = [];
        $unfilledCities = 0;
        foreach ($candidates as $c) {
            $city = trim($c->city ?? '');
            if (!empty($city) && !in_array(strtolower($city), ['-', 'tidak ada', 'belum diisi'])) {
                $city = ucwords(strtolower($city));
                $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
            } else {
                $unfilledCities++;
            }
        }
        arsort($cityCounts);
        $topCities = array_slice($cityCounts, 0, 10, true);

        // 3. District / Kecamatan Distribution
        $districtCounts = [];
        $unfilledDistricts = 0;
        foreach ($candidates as $c) {
            $district = trim($c->kecamatan ?? '');
            if (!empty($district) && !in_array(strtolower($district), ['-', 'tidak ada', 'belum diisi'])) {
                $district = ucwords(strtolower($district));
                $districtCounts[$district] = ($districtCounts[$district] ?? 0) + 1;
            } else {
                $unfilledDistricts++;
            }
        }
        arsort($districtCounts);
        $topDistricts = array_slice($districtCounts, 0, 10, true);

        // 4. Marketing Information Sources (Real survey responses if present)
        $sourceCounts = [];
        $totalSurveyResponses = 0;
        foreach ($candidates as $c) {
            $source = null;
            if (!empty($c->additional_info) && is_array($c->additional_info)) {
                $source = $c->additional_info['info_source'] 
                    ?? $c->additional_info['sumber_informasi'] 
                    ?? $c->additional_info['marketing_source'] 
                    ?? null;
            }
            if (!empty($source) && !in_array(strtolower(trim($source)), ['-', 'tidak ada'])) {
                $source = ucwords(strtolower(trim($source)));
                $sourceCounts[$source] = ($sourceCounts[$source] ?? 0) + 1;
                $totalSurveyResponses++;
            }
        }
        arsort($sourceCounts);

        $selectedPeriod = SpmbPeriod::find($selectedPeriodId);

        return view('admin.reports-demographics', compact(
            'candidates',
            'totalCandidates',
            'topSchools',
            'schoolCounts',
            'unfilledSchools',
            'totalFilledSchools',
            'topCities',
            'cityCounts',
            'unfilledCities',
            'topDistricts',
            'districtCounts',
            'unfilledDistricts',
            'sourceCounts',
            'totalSurveyResponses',
            'units',
            'selectedPeriod'
        ));
    }
}
