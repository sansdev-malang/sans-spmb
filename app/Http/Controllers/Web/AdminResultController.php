<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbUnit;
use App\Models\SpmbWave;

class AdminResultController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $units = SpmbUnit::where('is_active', true)->get();
        if (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $units = $units->where('id', auth()->user()->spmb_unit_id);
        }

        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'payments'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereIn('registration_status', ['taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed', 'rejected']);

        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === 'passed') {
                $query->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed']);
            } elseif ($st === 'rejected') {
                $query->where('registration_status', 'rejected');
            } elseif ($st === 'pending') {
                $query->where('registration_status', 'taaruf_scheduled');
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%" . ltrim(preg_replace('/[^0-9]/', '', $search), '0') . "%")
                  ->orWhere('parent_phone', 'like', "%{$search}%");
            });
        }

        $candidates = $query->latest('updated_at')->paginate(25)->withQueryString();

        $allCands = Registration::scopedByAdmin()
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereIn('registration_status', ['taaruf_scheduled', 'taaruf_completed', 'agreement_signed', 'completed', 'rejected'])
            ->get();

        $stats = [
            'total' => $allCands->count(),
            'passed' => $allCands->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed'])->count(),
            'rejected' => $allCands->where('registration_status', 'rejected')->count(),
            'pending' => $allCands->where('registration_status', 'taaruf_scheduled')->count(),
        ];

        $waves = SpmbWave::where('is_active', true)->get();
        $selectedPeriod = SpmbPeriod::find($selectedPeriodId);

        return view('admin.results', compact(
            'candidates',
            'stats',
            'units',
            'waves',
            'selectedPeriod'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:passed,rejected,pending',
            'committee_notes' => 'nullable|string|max:500',
        ]);

        $registration = Registration::scopedByAdmin()->findOrFail($id);

        if ($request->status === 'passed') {
            if (!in_array($registration->registration_status, ['agreement_signed', 'completed'])) {
                $registration->registration_status = 'taaruf_completed';
            }
        } elseif ($request->status === 'rejected') {
            $registration->registration_status = 'rejected';
        } elseif ($request->status === 'pending') {
            $registration->registration_status = 'taaruf_scheduled';
        }

        if ($request->has('committee_notes')) {
            $registration->committee_notes = $request->committee_notes;
        }

        $registration->save();

        return back()->with('success', 'Status kelulusan siswa ' . $registration->candidate_name . ' berhasil diperbarui.');
    }
}
