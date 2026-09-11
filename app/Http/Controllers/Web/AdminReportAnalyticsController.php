<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbUnit;
use App\Models\SpmbWave;
use App\Models\SpmbGrade;
use Illuminate\Support\Facades\DB;

class AdminReportAnalyticsController extends Controller
{
    /**
     * Display Registration Conversion Funnel & Class Quota Reports.
     */
    public function registrations(Request $request)
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
            ->with(['unit', 'grade', 'classProgram', 'wave'])
            ->where('spmb_period_id', $selectedPeriodId);

        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }

        $candidates = $query->get();

        // Conversion Funnel Stats
        $totalRegistered = $candidates->count();
        $totalVerified = $candidates->whereIn('registration_status', ['verified', 'taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed'])->count();
        $totalTaaruf = $candidates->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed'])->count();
        $totalCompleted = $candidates->where('registration_status', 'completed')->count();

        // Gender Distribution
        $maleCount = $candidates->filter(fn($c) => in_array(strtolower($c->gender ?? ''), ['l', 'laki-laki', 'male']))->count();
        $femaleCount = $candidates->filter(fn($c) => in_array(strtolower($c->gender ?? ''), ['p', 'perempuan', 'female']))->count();
        $unknownGenderCount = $totalRegistered - ($maleCount + $femaleCount);

        // Unit & Quota Matrix
        $unitStats = [];
        foreach ($units as $u) {
            $uCands = $candidates->where('spmb_unit_id', $u->id);
            $uRegistered = $uCands->count();
            $uVerified = $uCands->whereIn('registration_status', ['verified', 'taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed'])->count();
            $uCompleted = $uCands->where('registration_status', 'completed')->count();
            
            // Grades in this unit
            $grades = SpmbGrade::where('spmb_unit_id', $u->id)->where('is_active', true)->get();
            $gradeStats = [];
            foreach ($grades as $g) {
                $gCands = $uCands->where('spmb_grade_id', $g->id);
                $gradeStats[] = [
                    'grade' => $g,
                    'registered' => $gCands->count(),
                    'completed' => $gCands->where('registration_status', 'completed')->count(),
                ];
            }

            $unitStats[] = [
                'unit' => $u,
                'registered' => $uRegistered,
                'verified' => $uVerified,
                'completed' => $uCompleted,
                'grades' => $gradeStats,
            ];
        }

        $waves = SpmbWave::orderBy('id', 'asc')->get();
        $selectedPeriod = SpmbPeriod::find($selectedPeriodId);

        return view('admin.reports-registrations', compact(
            'totalRegistered',
            'totalVerified',
            'totalTaaruf',
            'totalCompleted',
            'maleCount',
            'femaleCount',
            'unknownGenderCount',
            'unitStats',
            'units',
            'waves',
            'selectedPeriod'
        ));
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
