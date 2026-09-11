<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbType;
use App\Models\SpmbClassProgram;
use App\Models\SpmbUnit;
use App\Models\SpmbFeeCategory;
use App\Services\SimpleXlsxService;
use Illuminate\Support\Str;

class AdminCandidateController extends Controller
{
    /**
     * Display active candidates (who have paid registration form fee).
     */
    public function index(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });
        
        $query = Registration::scopedByAdmin()
            ->with(['user', 'period', 'wave', 'type', 'payments'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereNotNull('candidate_name')
            ->whereHas('payments', function($q) {
                $q->where('payment_type', 'registration_fee')
                  ->where('status', 'success');
            });

        // Calculate Stats for Active Candidates with dynamic filters applied
        $baseStatsQuery = Registration::scopedByAdmin()
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereNotNull('candidate_name')
            ->whereHas('payments', function($q) {
                $q->where('payment_type', 'registration_fee')
                  ->where('status', 'success');
            });

        if ($request->filled('unit_id')) {
            $baseStatsQuery->where('spmb_unit_id', $request->unit_id);
        }
        if ($request->filled('wave_id')) {
            $baseStatsQuery->where('spmb_wave_id', $request->wave_id);
        }
        if ($request->filled('type_id')) {
            $baseStatsQuery->where('spmb_type_id', $request->type_id);
        }
        if ($request->filled('class_program_id')) {
            $baseStatsQuery->where('spmb_class_program_id', $request->class_program_id);
        }
        if ($request->filled('start_date')) {
            $baseStatsQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseStatsQuery->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $baseStatsQuery->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $totalCount = (clone $baseStatsQuery)->count();
        $maleCount = (clone $baseStatsQuery)->whereIn('gender', ['L', 'male', 'Laki-laki', 'laki-laki', 'Laki-Laki'])->count();
        $femaleCount = (clone $baseStatsQuery)->whereIn('gender', ['P', 'female', 'Perempuan', 'perempuan'])->count();
        $verifiedCount = (clone $baseStatsQuery)->whereIn('registration_status', ['verified', 'taaruf_completed', 'agreement_signed', 'completed'])->count();
        $pendingCount = (clone $baseStatsQuery)->where('registration_status', 'submitted')->count();

        $stats = [
            'total' => $totalCount,
            'male' => $maleCount,
            'female' => $femaleCount,
            'verified' => $verifiedCount,
            'pending' => $pendingCount,
        ];

        // Calculate Wave Stats
        $waveStats = SpmbWave::all()->map(function($w) use ($baseStatsQuery) {
            return [
                'name' => $w->name,
                'count' => (clone $baseStatsQuery)->where('spmb_wave_id', $w->id)->count()
            ];
        })->filter(function($item) {
            return $item['count'] > 0;
        });

        // Calculate Jalur (Type) Stats
        $typeStats = SpmbType::all()->map(function($t) use ($baseStatsQuery) {
            return [
                'name' => $t->name,
                'count' => (clone $baseStatsQuery)->where('spmb_type_id', $t->id)->count()
            ];
        })->filter(function($item) {
            return $item['count'] > 0;
        });

        // Calculate Program Kelas Stats
        $classProgramStats = SpmbClassProgram::all()->map(function($cp) use ($baseStatsQuery) {
            return [
                'name' => $cp->name,
                'count' => (clone $baseStatsQuery)->where('spmb_class_program_id', $cp->id)->count()
            ];
        })->filter(function($item) {
            return $item['count'] > 0;
        });

        // Calculate Stage Counts for Pills
        $stageCounts = [
            'all' => (clone $baseStatsQuery)->count(),
            'draft' => (clone $baseStatsQuery)->whereIn('registration_status', ['draft', 'failed'])->count(),
            'submitted' => (clone $baseStatsQuery)->where('registration_status', 'submitted')->count(),
            'verified' => (clone $baseStatsQuery)->where('registration_status', 'verified')->count(),
            'taaruf_completed' => (clone $baseStatsQuery)->where('registration_status', 'taaruf_completed')->count(),
            'agreement_signed' => (clone $baseStatsQuery)->where('registration_status', 'agreement_signed')->count(),
            'completed' => (clone $baseStatsQuery)->where('registration_status', 'completed')->count(),
        ];

        // Search by Name, WhatsApp, or NIK
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter by Stage / Status Pill
        if ($request->filled('stage') && $request->stage !== 'all') {
            if ($request->stage === 'draft') {
                $query->whereIn('registration_status', ['draft', 'failed']);
            } else {
                $query->where('registration_status', $request->stage);
            }
        }

        // Filter by Unit/Jenjang School
        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        // Filter by Registration Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by Gender
        if ($request->filled('gender')) {
            $g = strtolower($request->gender);
            if (in_array($g, ['l', 'male', 'laki-laki'])) {
                $query->whereIn('gender', ['L', 'male', 'Laki-laki', 'laki-laki', 'Laki-Laki']);
            } elseif (in_array($g, ['p', 'female', 'perempuan'])) {
                $query->whereIn('gender', ['P', 'female', 'Perempuan', 'perempuan']);
            } else {
                $query->where('gender', $request->gender);
            }
        }

        // Filter by Wave
        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }

        // Filter by Registration Type
        if ($request->filled('type_id')) {
            $query->where('spmb_type_id', $request->type_id);
        }

        // Filter by Class Program
        if ($request->filled('class_program_id')) {
            $query->where('spmb_class_program_id', $request->class_program_id);
        }

        // Filter by Document Upload Status
        if ($request->filled('doc_status')) {
            if ($request->doc_status === 'complete') {
                $query->whereNotNull('birth_certificate_path')
                      ->whereNotNull('family_card_path');
            } elseif ($request->doc_status === 'incomplete') {
                $query->where(function($q) {
                    $q->whereNull('birth_certificate_path')
                      ->orWhereNull('family_card_path');
                });
            }
        }

        // Per page limit
        $perPage = intval($request->get('per_page', 10));
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $candidates = $query->latest()->paginate($perPage)->withQueryString();

        return view('admin.candidates', compact('candidates', 'stats', 'waveStats', 'typeStats', 'classProgramStats', 'stageCounts'));
    }

    /**
     * Display registration history log.
     */
    public function history(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $query = Registration::scopedByAdmin()
            ->with(['user', 'period', 'wave', 'type', 'payments'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereNotNull('candidate_name');

        // Search by Name, WhatsApp, or NIK
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter by Unit/Jenjang School
        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        // Filter by SPMB Process Stage
        if ($request->filled('status')) {
            $query->where('registration_status', $request->status);
        }

        // Filter by Registration Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by Gender
        if ($request->filled('gender')) {
            $g = strtolower($request->gender);
            if (in_array($g, ['l', 'male', 'laki-laki'])) {
                $query->whereIn('gender', ['L', 'male', 'Laki-laki', 'laki-laki', 'Laki-Laki']);
            } elseif (in_array($g, ['p', 'female', 'perempuan'])) {
                $query->whereIn('gender', ['P', 'female', 'Perempuan', 'perempuan']);
            } else {
                $query->where('gender', $request->gender);
            }
        }

        // Filter by Wave
        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }

        // Filter by Registration Type
        if ($request->filled('type_id')) {
            $query->where('spmb_type_id', $request->type_id);
        }

        // Filter by Class Program
        if ($request->filled('class_program_id')) {
            $query->where('spmb_class_program_id', $request->class_program_id);
        }

        // Filter by Document Upload Status
        if ($request->filled('doc_status')) {
            if ($request->doc_status === 'complete') {
                $query->whereNotNull('birth_certificate_path')
                      ->whereNotNull('family_card_path');
            } elseif ($request->doc_status === 'incomplete') {
                $query->where(function($q) {
                    $q->whereNull('birth_certificate_path')
                      ->orWhereNull('family_card_path');
                });
            }
        }

        // Per page limit
        $perPage = intval($request->get('per_page', 10));
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $candidates = $query->latest()->paginate($perPage)->withQueryString();

        return view('admin.history', compact('candidates'));
    }

    /**
     * Set candidate admission status directly to completed via dispensation override.
     */
    public function manualAccept(Request $request, $id)
    {
        $registration = Registration::scopedByAdmin()->findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $candidateName = $registration->candidate_name ?? 'ID: ' . $registration->id;
        $reasonText = trim($validated['reason']);

        $registration->update([
            'registration_status' => 'completed',
            'is_dispensation' => true,
            'dispensation_reason' => $reasonText,
            'dispensation_approved_by' => auth()->id(),
            'dispensation_approved_at' => now(),
            'committee_notes' => 'Alhamdulillah, ananda resmi DITERIMA di Sekolah Anak Saleh melalui persetujuan kebijakan/dispensasi khusus Yayasan (' . $reasonText . ').',
        ]);

        \App\Models\SpmbActivityLog::log(
            'MANUAL_ADMISSION_DISPENSATION',
            "Menetapkan status Diterima (Dispensasi) untuk calon murid {$candidateName} dengan alasan: {$reasonText}"
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Calon murid {$candidateName} berhasil ditetapkan DITERIMA melalui dispensasi khusus ({$reasonText}).",
                'data' => [
                    'id' => $registration->id,
                    'registration_status' => $registration->registration_status,
                    'is_dispensation' => (bool)$registration->is_dispensation,
                    'dispensation_reason' => $registration->dispensation_reason,
                    'remaining_balance' => (float) $registration->remaining_final_fee,
                ]
            ]);
        }

        return redirect()->back()->with('success', "Calon murid {$candidateName} berhasil ditetapkan DITERIMA melalui dispensasi khusus ({$reasonText}).");
    }

    /**
     * Revert dispensation acceptance status.
     */
    public function revertManualAccept(Request $request, $id)
    {
        $registration = Registration::scopedByAdmin()->findOrFail($id);

        if (!$registration->is_dispensation) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Calon murid ini tidak diterima melalui jalur dispensasi manual.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Calon murid ini tidak diterima melalui jalur dispensasi manual.');
        }

        $candidateName = $registration->candidate_name ?? 'ID: ' . $registration->id;
        $totalPaid = $registration->total_paid_final_fee;
        $totalRequired = $registration->net_fee;

        // If candidate already paid full, they remain naturally completed
        if ($totalPaid >= $totalRequired && $totalRequired > 0) {
            $registration->update([
                'is_dispensation' => false,
                'dispensation_reason' => null,
                'dispensation_approved_by' => null,
                'dispensation_approved_at' => null,
            ]);
        } else {
            // Revert back to agreement_signed if agreement was signed, else taaruf_completed
            $newStatus = !empty($registration->signed_at) ? 'agreement_signed' : 'taaruf_completed';
            $registration->update([
                'registration_status' => $newStatus,
                'is_dispensation' => false,
                'dispensation_reason' => null,
                'dispensation_approved_by' => null,
                'dispensation_approved_at' => null,
                'committee_notes' => null,
            ]);
        }

        \App\Models\SpmbActivityLog::log(
            'REVERT_MANUAL_ADMISSION_DISPENSATION',
            "Membatalkan status dispensasi penerimaan untuk calon murid {$candidateName}"
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Dispensasi penerimaan calon murid {$candidateName} berhasil dibatalkan.",
                'data' => [
                    'id' => $registration->id,
                    'registration_status' => $registration->registration_status,
                    'is_dispensation' => false,
                ]
            ]);
        }

        return redirect()->back()->with('success', "Dispensasi penerimaan calon murid {$candidateName} berhasil dibatalkan.");
    }

    /**
     * Export filtered candidates data to Excel (.xls with formatting).
     */
    public function export(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $query = Registration::scopedByAdmin()
            ->with(['user', 'period', 'unit', 'grade', 'wave', 'type', 'classProgram', 'extraServices', 'payments'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereNotNull('candidate_name')
            ->whereHas('payments', function($q) {
                $q->where('payment_type', 'registration_fee')
                  ->where('status', 'success');
            });

        // Search by Name, WhatsApp, or NIK
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter by Stage / Status Pill
        if ($request->filled('stage') && $request->stage !== 'all') {
            if ($request->stage === 'draft') {
                $query->whereIn('registration_status', ['draft', 'failed']);
            } else {
                $query->where('registration_status', $request->stage);
            }
        }

        // Filter by Unit
        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        // Filter by Registration Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by Gender
        if ($request->filled('gender')) {
            $g = strtolower($request->gender);
            if (in_array($g, ['l', 'male', 'laki-laki'])) {
                $query->whereIn('gender', ['L', 'male', 'Laki-laki', 'laki-laki', 'Laki-Laki']);
            } elseif (in_array($g, ['p', 'female', 'perempuan'])) {
                $query->whereIn('gender', ['P', 'female', 'Perempuan', 'perempuan']);
            } else {
                $query->where('gender', $request->gender);
            }
        }

        // Filter by Wave
        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }

        // Filter by Registration Type
        if ($request->filled('type_id')) {
            $query->where('spmb_type_id', $request->type_id);
        }

        // Filter by Class Program
        if ($request->filled('class_program_id')) {
            $query->where('spmb_class_program_id', $request->class_program_id);
        }

        // Filter by Document Upload Status
        if ($request->filled('doc_status')) {
            if ($request->doc_status === 'complete') {
                $query->whereNotNull('birth_certificate_path')
                      ->whereNotNull('family_card_path');
            } elseif ($request->doc_status === 'incomplete') {
                $query->where(function($q) {
                    $q->whereNull('birth_certificate_path')
                      ->orWhereNull('family_card_path');
                });
            }
        }

        $candidates = $query->orderBy('created_at', 'desc')->get();

        $unitCode = 'ALL';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        } elseif (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $u = SpmbUnit::find(auth()->user()->spmb_unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        }

        $period = SpmbPeriod::find($selectedPeriodId);
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';
        $filename = 'Data-Calon-Murid-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.xlsx';

        $regFeeLabel = SpmbFeeCategory::getRegistrationCategoryName();
        $tuitionFeeLabel = SpmbFeeCategory::getTuitionCategoryName();

        $columns = [
            'No.',
            'No. Registrasi',
            'Tanggal Daftar',
            'Tahun Ajaran',
            'Unit Sekolah',
            'Tingkat / Kelas',
            'Gelombang',
            'Jalur Masuk',
            'Program Kelas',
            'Layanan Tambahan',
            'Status Registrasi',
            'Nama Lengkap Siswa',
            'Nama Panggilan',
            'Jenis Kelamin',
            'NIK Siswa',
            'No. Kartu Keluarga',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Usia',
            'Agama',
            'Asal Sekolah',
            'Alamat Lengkap',
            'RT / RW',
            'Kelurahan',
            'Kecamatan',
            'Kota / Kabupaten',
            'Provinsi',
            'Nama Ayah',
            'NIK Ayah',
            'Pekerjaan Ayah',
            'No. WhatsApp Ayah',
            'Nama Ibu',
            'NIK Ibu',
            'Pekerjaan Ibu',
            'No. WhatsApp Ibu',
            'Nama Wali',
            'No. WhatsApp Wali',
            'Status ' . $regFeeLabel,
            'Tagihan ' . $tuitionFeeLabel . ' (Bruto)',
            'Diskon / Keringanan (Rp)',
            'Tagihan ' . $tuitionFeeLabel . ' Bersih (Netto)',
            'Total ' . $tuitionFeeLabel . ' Terbayar (Rp)',
            'Sisa Piutang ' . $tuitionFeeLabel . ' (Rp)',
            'Status Pelunasan ' . $tuitionFeeLabel,
            'Tanggal & Waktu Observasi',
            'Lokasi Observasi',
            'Penguji / Pewawancara',
            'Catatan Observasi'
        ];

        $rows = [];
        $i = 1;
        foreach ($candidates as $c) {
            $isDSPStage = in_array($c->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']);
            $gross = $isDSPStage ? $c->getGrossFee() : 0;
            $discount = $isDSPStage ? $c->total_discount : 0;
            $net = $isDSPStage ? $c->net_fee : 0;
            $paidDSP = $isDSPStage ? $c->total_paid_final_fee : 0;
            $remaining = $isDSPStage ? $c->remaining_balance : 0;

            $statusDSP = 'Belum Tahap Biaya Masuk';
            if ($isDSPStage) {
                if ($c->is_dispensation) {
                    $statusDSP = 'DISPENSASI';
                } elseif ($remaining <= 0 && $net > 0 && $paidDSP > 0) {
                    $statusDSP = 'LUNAS';
                } elseif ($paidDSP > 0 && $remaining > 0) {
                    $statusDSP = 'CICILAN / SEBAGIAN';
                } else {
                    $statusDSP = 'BELUM BAYAR';
                }
            }

            $age = '-';
            if ($c->birth_date) {
                $diff = $c->birth_date->diff(now());
                $age = $diff->y . ' Thn ' . $diff->m . ' Bln';
            }

            $formPayment = $c->payments->where('payment_type', 'registration_fee')->whereIn('status', ['success', 'settled', 'paid'])->first();
            $formStatus = $formPayment ? 'LUNAS' : ($c->payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR');

            $genderLabel = '-';
            if ($c->gender) {
                $g = strtolower($c->gender);
                if (in_array($g, ['l', 'male', 'laki-laki'])) {
                    $genderLabel = 'Laki-laki';
                } elseif (in_array($g, ['p', 'female', 'perempuan'])) {
                    $genderLabel = 'Perempuan';
                } else {
                    $genderLabel = $c->gender;
                }
            }

            $obsTime = '-';
            if ($c->observation_date) {
                $obsTime = $c->observation_date->format('d/m/Y') . ($c->observation_time ? ' ' . $c->observation_time : '');
            }

            $rows[] = [
                $i++,
                $c->id_label ?? '-',
                $c->created_at ? $c->created_at->format('d/m/Y H:i') : '-',
                $c->period->name ?? ($c->period->year ?? '-'),
                $c->unit->name ?? '-',
                $c->grade->name ?? ($c->admission_level ?? '-'),
                $c->wave->name ?? '-',
                $c->type->name ?? '-',
                $c->classProgram->name ?? ($c->class_program ?? 'Reguler'),
                $c->extraServices->pluck('name')->implode(', ') ?: '-',
                strtoupper($c->registration_status ?? '-'),
                $c->candidate_name ?? '-',
                $c->nickname ?: '-',
                $genderLabel,
                $c->nik ?: '-',
                $c->family_card_no ?: '-',
                $c->birth_place ?: '-',
                $c->birth_date ? $c->birth_date->format('d/m/Y') : '-',
                $age,
                $c->religion ?: '-',
                $c->previous_school ?: '-',
                $c->getFullCandidateAddress() ?: ($c->address ?: '-'),
                ($c->rt ? 'RT ' . $c->rt : '') . ($c->rw ? ' RW ' . $c->rw : '') ?: '-',
                $c->kelurahan ?: '-',
                $c->kecamatan ?: '-',
                $c->city ?: '-',
                $c->province ?: '-',
                $c->father_name ?: '-',
                $c->father_nik ?: '-',
                $c->father_job ?: '-',
                $c->father_phone ?: '-',
                $c->mother_name ?: '-',
                $c->mother_nik ?: '-',
                $c->mother_job ?: '-',
                $c->mother_phone ?: '-',
                $c->guardian_name ?: '-',
                $c->guardian_phone ?: '-',
                $formStatus,
                $gross,
                $discount,
                $net,
                $paidDSP,
                $remaining,
                $statusDSP,
                $obsTime,
                $c->observation_room ?: ($c->observation_location ?: '-'),
                $c->observation_interviewer ?: '-',
                $c->observation_notes ?: '-'
            ];
        }

        return SimpleXlsxService::download($columns, $rows, $filename, 'Data Calon Murid');
    }

    /**
     * Export / Print filtered candidates executive summary report to PDF (A4 Landscape).
     */
    public function exportPdf(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $query = Registration::scopedByAdmin()
            ->with(['user', 'period', 'unit', 'grade', 'wave', 'type', 'classProgram', 'extraServices', 'payments'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereNotNull('candidate_name')
            ->whereHas('payments', function($q) {
                $q->where('payment_type', 'registration_fee')
                  ->where('status', 'success');
            });

        // Search by Name, WhatsApp, or NIK
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter by Stage / Status Pill
        if ($request->filled('stage') && $request->stage !== 'all') {
            if ($request->stage === 'draft') {
                $query->whereIn('registration_status', ['draft', 'failed']);
            } else {
                $query->where('registration_status', $request->stage);
            }
        }

        // Filter by Unit
        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        // Filter by Registration Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by Gender
        if ($request->filled('gender')) {
            $g = strtolower($request->gender);
            if (in_array($g, ['l', 'male', 'laki-laki'])) {
                $query->whereIn('gender', ['L', 'male', 'Laki-laki', 'laki-laki', 'Laki-Laki']);
            } elseif (in_array($g, ['p', 'female', 'perempuan'])) {
                $query->whereIn('gender', ['P', 'female', 'Perempuan', 'perempuan']);
            } else {
                $query->where('gender', $request->gender);
            }
        }

        // Filter by Wave
        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }

        // Filter by Registration Type
        if ($request->filled('type_id')) {
            $query->where('spmb_type_id', $request->type_id);
        }

        // Filter by Class Program
        if ($request->filled('class_program_id')) {
            $query->where('spmb_class_program_id', $request->class_program_id);
        }

        // Filter by Document Upload Status
        if ($request->filled('doc_status')) {
            if ($request->doc_status === 'complete') {
                $query->whereNotNull('birth_certificate_path')
                      ->whereNotNull('family_card_path');
            } elseif ($request->doc_status === 'incomplete') {
                $query->where(function($q) {
                    $q->whereNull('birth_certificate_path')
                      ->orWhereNull('family_card_path');
                });
            }
        }

        $candidates = $query->orderBy('created_at', 'desc')->get();

        // Calculate KPI Stats for filtered set
        $stats = [
            'total' => $candidates->count(),
            'male' => $candidates->whereIn('gender', ['L', 'male', 'Laki-laki', 'laki-laki', 'Laki-Laki'])->count(),
            'female' => $candidates->whereIn('gender', ['P', 'female', 'Perempuan', 'perempuan'])->count(),
            'verified' => $candidates->where('is_verified', true)->count(),
            'pending' => $candidates->where('is_verified', false)->count(),
        ];

        // Distribution stats
        $waveStats = SpmbWave::all()->map(function($w) use ($candidates) {
            return [
                'name' => $w->name,
                'count' => $candidates->where('spmb_wave_id', $w->id)->count(),
            ];
        })->where('count', '>', 0)->values();

        $typeStats = SpmbType::all()->map(function($t) use ($candidates) {
            return [
                'name' => $t->name,
                'count' => $candidates->where('spmb_type_id', $t->id)->count(),
            ];
        })->where('count', '>', 0)->values();

        $stageCounts = [
            'all' => $stats['total'],
            'draft' => $candidates->whereIn('registration_status', ['draft', 'failed'])->count(),
            'submitted' => $candidates->where('registration_status', 'submitted')->count(),
            'verified' => $candidates->where('registration_status', 'verified')->count(),
            'taaruf_completed' => $candidates->where('registration_status', 'taaruf_completed')->count(),
            'agreement_signed' => $candidates->where('registration_status', 'agreement_signed')->count(),
            'completed' => $candidates->where('registration_status', 'completed')->count(),
        ];

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

        $period = SpmbPeriod::find($selectedPeriodId);
        $periodName = $period ? ($period->name ?? $period->year) : 'SPMB';
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';
        $filename = 'Laporan-Calon-Murid-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.pdf';

        $regFeeLabel = SpmbFeeCategory::getRegistrationCategoryName();
        $printedAt = now()->translatedFormat('d F Y, H:i') . ' WIB';
        $printedBy = auth()->user()->name ?? 'Administrator';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.candidates-pdf', compact(
            'candidates',
            'stats',
            'waveStats',
            'typeStats',
            'stageCounts',
            'periodName',
            'unitFilterLabel',
            'regFeeLabel',
            'printedAt',
            'printedBy'
        ))->setPaper('a4', 'landscape');

        return response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
