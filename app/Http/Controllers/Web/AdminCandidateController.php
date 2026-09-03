<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbType;
use App\Models\SpmbClassProgram;

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
}
