<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbType;
use App\Models\SpmbFee;
use App\Models\SpmbUnit;
use App\Models\SpmbGrade;
use App\Models\SpmbClassProgram;
use App\Models\SpmbExtraService;
use Illuminate\Http\Request;

class SpmbRegistrationSettingsController extends Controller
{
    public function index()
    {
        $periods = SpmbPeriod::all();
        $waves = SpmbWave::all();
        $types = SpmbType::all();
        $units = SpmbUnit::all();
        $grades = SpmbGrade::with('unit')->get();
        $classPrograms = SpmbClassProgram::all();
        $extraServices = SpmbExtraService::all();
        $gateways = \App\Models\PaymentGateway::where('is_active', true)->with('paymentChannels')->get();

        // Eager load all fee categories with their respective fee items and unit relationships
        $feeCategories = \App\Models\SpmbFeeCategory::with(['fees' => function($q) {
            $q->with('unit');
        }])->get();

        return view('admin.settings-registration', compact(
            'periods', 'waves', 'types', 'feeCategories',
            'units', 'grades', 'classPrograms', 'extraServices', 'gateways'
        ));
    }

    public function update(Request $request)
    {
        $activePeriodIds = $request->input('active_periods', []);
        $activeWaveIds = $request->input('active_waves', []);
        $activeTypeIds = $request->input('active_types', []);
        $activeFeeIds = $request->input('active_fees', []);
        $activeUnitIds = $request->input('active_units', []);
        $activeGradeIds = $request->input('active_grades', []);
        $activeProgramIds = $request->input('active_programs', []);
        $activeServiceIds = $request->input('active_services', []);
        $activeChannelIds = $request->input('active_channels', []);

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
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Biaya Komponen yang aktif.');
        }
        if (empty($activeUnitIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Unit Sekolah yang aktif.');
        }
        if (empty($activeGradeIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Tingkatan Kelas yang aktif.');
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

        // 5. Units
        SpmbUnit::query()->update(['is_active' => false]);
        SpmbUnit::whereIn('id', $activeUnitIds)->update(['is_active' => true]);

        // 6. Grades
        SpmbGrade::query()->update(['is_active' => false]);
        SpmbGrade::whereIn('id', $activeGradeIds)->update(['is_active' => true]);

        // 7. Class Programs
        SpmbClassProgram::query()->update(['is_active' => false]);
        SpmbClassProgram::whereIn('id', $activeProgramIds)->update(['is_active' => true]);

        // 8. Extra Services
        SpmbExtraService::query()->update(['is_active' => false]);
        SpmbExtraService::whereIn('id', $activeServiceIds)->update(['is_active' => true]);

        // 9. Payment Channels
        \App\Models\SpmbPaymentChannel::query()->update(['is_active' => false]);
        if (!empty($activeChannelIds)) {
            \App\Models\SpmbPaymentChannel::whereIn('id', $activeChannelIds)->update(['is_active' => true]);
        }

        $activeTab = $request->input('active_tab', 'jalur_gelombang');
        return redirect()->route('admin.spmb-settings.registration', ['tab' => $activeTab])->with('success', 'Status aktifasi pendaftaran berhasil diperbarui.');
    }
}
