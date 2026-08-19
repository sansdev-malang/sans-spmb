<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbType;
use App\Models\SpmbFee;
use Illuminate\Http\Request;

class SpmbRegistrationSettingsController extends Controller
{
    public function index()
    {
        $periods = SpmbPeriod::all();
        $waves = SpmbWave::all();
        $types = SpmbType::all();
        $fees = SpmbFee::all();

        return view('admin.settings-registration', compact('periods', 'waves', 'types', 'fees'));
    }

    public function update(Request $request)
    {
        $activePeriodIds = $request->input('active_periods', []);
        $activeWaveIds = $request->input('active_waves', []);
        $activeTypeIds = $request->input('active_types', []);
        $activeFeeIds = $request->input('active_fees', []);

        // Validation: At least one item of each configuration type must be active
        if (empty($activePeriodIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Periode Akademik yang aktif.');
        }
        if (empty($activeWaveIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Gelombang Pendaftaran yang aktif.');
        }
        if (empty($activeTypeIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Jenis Pendaftaran yang aktif.');
        }
        if (empty($activeFeeIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Biaya Tambahan yang aktif.');
        }

        // 1. Periods (Multiple active periods supported)
        SpmbPeriod::query()->update(['is_active' => false]);
        SpmbPeriod::whereIn('id', $activePeriodIds)->update(['is_active' => true]);

        // 2. Waves
        SpmbWave::query()->update(['is_active' => false]);
        SpmbWave::whereIn('id', $activeWaveIds)->update(['is_active' => true]);

        // 3. Types
        SpmbType::query()->update(['is_active' => false]);
        SpmbType::whereIn('id', $activeTypeIds)->update(['is_active' => true]);

        // 4. Fees
        SpmbFee::query()->update(['is_active' => false]);
        SpmbFee::whereIn('id', $activeFeeIds)->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Status aktifasi pendaftaran berhasil diperbarui.');
    }
}
