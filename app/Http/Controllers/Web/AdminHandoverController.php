<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbUnit;
use App\Models\SpmbWave;

class AdminHandoverController extends Controller
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
            ->whereIn('registration_status', ['completed', 'agreement_signed', 'taaruf_completed']);

        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%" . ltrim(preg_replace('/[^0-9]/', '', $search), '0') . "%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('mother_name', 'like', "%{$search}%");
            });
        }

        $readyCandidates = $query->latest('updated_at')->paginate(30)->withQueryString();

        $allReady = Registration::scopedByAdmin()
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereIn('registration_status', ['completed', 'agreement_signed', 'taaruf_completed'])
            ->get();

        $totalLunas = $allReady->filter(fn($c) => $c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0)->count();

        $selectedPeriod = SpmbPeriod::find($selectedPeriodId);

        return view('admin.handover', compact(
            'readyCandidates',
            'allReady',
            'totalLunas',
            'units',
            'selectedPeriod'
        ));
    }

    public function export(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereIn('registration_status', ['completed', 'agreement_signed', 'taaruf_completed']);

        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        $candidates = $query->get();

        $unitName = 'Semua_Unit';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) $unitName = str_replace(' ', '_', $u->name);
        }

        $csvFileName = 'handover_siswa_baru_' . $unitName . '_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'ID SPMB', 'No Registrasi', 'Nama Lengkap Siswa', 'Nama Panggilan', 'Jenis Kelamin', 'NISN', 'NIK Siswa', 
            'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Alamat Lengkap', 'Unit Tujuan', 'Jenjang/Tingkat', 'Program Kelas',
            'Nama Ayah', 'Pekerjaan Ayah', 'No HP/WA Ayah', 'Nama Ibu', 'Pekerjaan Ibu', 'No HP/WA Ibu', 'Asal Sekolah',
            'Status Pembayaran', 'Status SPMB'
        ];

        $callback = function() use ($candidates, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($candidates as $c) {
                $statusPay = 'Belum Lunas';
                if ($c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0) {
                    $statusPay = 'Lunas';
                } elseif ($c->total_paid_final_fee > 0) {
                    $statusPay = 'Cicilan';
                }

                fputcsv($file, [
                    $c->id,
                    'SPMB-' . str_pad($c->id, 5, '0', STR_PAD_LEFT),
                    $c->candidate_name,
                    $c->nickname ?? '',
                    $c->gender ?? '',
                    $c->nisn ?? '',
                    $c->nik ?? '',
                    $c->birth_place ?? '',
                    $c->birth_date ? \Carbon\Carbon::parse($c->birth_date)->format('d/m/Y') : '',
                    $c->religion ?? 'Islam',
                    $c->address ?? '',
                    $c->unit->name ?? '-',
                    $c->grade->name ?? '-',
                    $c->classProgram->name ?? '-',
                    $c->father_name ?? '',
                    $c->father_job ?? '',
                    $c->parent_phone ?? $c->father_phone ?? '',
                    $c->mother_name ?? '',
                    $c->mother_job ?? '',
                    $c->mother_phone ?? '',
                    $c->previous_school ?? '',
                    $statusPay,
                    strtoupper($c->registration_status)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
