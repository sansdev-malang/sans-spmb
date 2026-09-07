<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbUnit;
use App\Models\SpmbWave;

class AdminBroadcastController extends Controller
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

        $targetAudience = $request->input('target', 'all');

        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'wave', 'classProgram'])
            ->where('spmb_period_id', $selectedPeriodId);

        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }

        // Target filtering
        if ($targetAudience === 'unverified') {
            $query->where('registration_status', 'submitted');
        } elseif ($targetAudience === 'taaruf_schedule') {
            $query->where('registration_status', 'verified')->whereNotNull('observation_date');
        } elseif ($targetAudience === 'passed_unpaid') {
            $query->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed']);
        } elseif ($targetAudience === 'has_receivable') {
            $query->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed']);
        }

        $allTargetCandidates = $query->get();

        if ($targetAudience === 'has_receivable') {
            $allTargetCandidates = $allTargetCandidates->filter(fn($c) => $c->remaining_balance > 0)->values();
        }

        // Default template options
        $defaultTemplates = [
            'unverified' => "Assalamu'alaikum Wr. Wb. Bapak/Ibu Wali dari *{{nama_siswa}}* (No. Daftar: *{{no_pendaftaran}}*).\n\nMohon untuk segera melengkapi berkas & formulir pendaftaran SPMB di unit *{{unit}}* agar dapat segera diproses verifikasi oleh panitia.\n\nTerima kasih.\n_Panitia SPMB Sekolah Anak Saleh_",
            'taaruf_schedule' => "Assalamu'alaikum Wr. Wb. Bapak/Ibu Wali dari *{{nama_siswa}}* (No. Daftar: *{{no_pendaftaran}}*).\n\nKami mengundang Bapak/Ibu dan ananda untuk mengikuti observasi & Ta'aruf calon siswa baru di unit *{{unit}}* pada:\n📅 Tanggal: *{{jadwal_taaruf}}*\n\nMohon hadir tepat waktu. Terima kasih.\n_Panitia SPMB Sekolah Anak Saleh_",
            'has_receivable' => "Assalamu'alaikum Wr. Wb. Bapak/Ibu Wali dari *{{nama_siswa}}* (No. Daftar: *{{no_pendaftaran}}*).\n\nKami informasikan bahwa ananda telah diterima di unit *{{unit}}*. Mengingat batas waktu daftar ulang, sisa tagihan DSP ananda sebesar *Rp {{sisa_tagihan}}*.\n\nPembayaran dapat dilakukan melalui portal SPMB: {{portal_url}}.\n\nTerima kasih.\n_Bagian Keuangan Sekolah Anak Saleh_",
            'general' => "Assalamu'alaikum Wr. Wb. Yth. Bapak/Ibu Wali dari *{{nama_siswa}}* (No. Daftar: *{{no_pendaftaran}}*).\n\nBerikut informasi penting terkait kegiatan SPMB di unit *{{unit}}*...\n\nTerima kasih.\n_Panitia SPMB Sekolah Anak Saleh_",
        ];

        $currentMessage = $request->input('message', $defaultTemplates[$targetAudience] ?? $defaultTemplates['general']);

        $waves = SpmbWave::where('is_active', true)->get();
        $selectedPeriod = SpmbPeriod::find($selectedPeriodId);

        return view('admin.broadcasts', compact(
            'allTargetCandidates',
            'targetAudience',
            'defaultTemplates',
            'currentMessage',
            'units',
            'waves',
            'selectedPeriod'
        ));
    }
}
