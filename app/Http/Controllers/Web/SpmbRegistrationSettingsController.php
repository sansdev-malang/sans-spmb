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
use App\Models\SpmbActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpmbRegistrationSettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $units = SpmbUnit::orderBy('id', 'asc')->get();

        if ($units->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada data unit sekolah yang tersedia.');
        }

        // Scope to user unit if not super admin
        if (!$isSuperAdmin) {
            $selectedUnitId = $user->spmb_unit_id ?: $units->first()->id;
        } else {
            $selectedUnitId = $request->get('unit_id', $units->first()->id);
            // Ensure selectedUnitId is valid
            if (!$units->contains('id', $selectedUnitId)) {
                $selectedUnitId = $units->first()->id;
            }
        }

        $selectedUnit = $units->firstWhere('id', $selectedUnitId) ?: $units->first();

        // Load Periods with unit-specific active status
        $periods = SpmbPeriod::orderBy('id', 'asc')->get()->map(function($p) use ($selectedUnitId) {
            $pivot = DB::table('spmb_period_unit')
                ->where('spmb_period_id', $p->id)
                ->where('spmb_unit_id', $selectedUnitId)
                ->first();
            $p->unit_is_active = $pivot ? (bool)$pivot->is_active : (bool)$p->is_active;
            return $p;
        });

        // Load Waves with unit-specific active status
        $waves = SpmbWave::orderBy('id', 'asc')->get()->map(function($w) use ($selectedUnitId) {
            $pivot = DB::table('spmb_wave_unit')
                ->where('spmb_wave_id', $w->id)
                ->where('spmb_unit_id', $selectedUnitId)
                ->first();
            $w->unit_is_active = $pivot ? (bool)$pivot->is_active : (bool)$w->is_active;
            return $w;
        });

        // Load Types with unit-specific active status
        $types = SpmbType::orderBy('id', 'asc')->get()->map(function($t) use ($selectedUnitId) {
            $pivot = DB::table('spmb_type_unit')
                ->where('spmb_type_id', $t->id)
                ->where('spmb_unit_id', $selectedUnitId)
                ->first();
            $t->unit_is_active = $pivot ? (bool)$pivot->is_active : (bool)$t->is_active;
            return $t;
        });

        // Load Class Programs with unit-specific active status
        $classPrograms = SpmbClassProgram::orderBy('id', 'asc')->get()->map(function($cp) use ($selectedUnitId) {
            $pivot = DB::table('spmb_class_program_unit')
                ->where('spmb_class_program_id', $cp->id)
                ->where('spmb_unit_id', $selectedUnitId)
                ->first();
            $cp->unit_is_active = $pivot ? (bool)$pivot->is_active : (bool)$cp->is_active;
            return $cp;
        });

        // Load Extra Services with unit-specific active status
        $extraServices = SpmbExtraService::where(function($q) use ($selectedUnitId) {
            $q->whereNull('spmb_unit_id')->orWhere('spmb_unit_id', $selectedUnitId);
        })->get()->map(function($es) use ($selectedUnitId) {
            $pivot = DB::table('spmb_extra_service_unit')
                ->where('spmb_extra_service_id', $es->id)
                ->where('spmb_unit_id', $selectedUnitId)
                ->first();
            $es->unit_is_active = $pivot ? (bool)$pivot->is_active : (bool)$es->is_active;
            return $es;
        });

        // Load Grades for this specific unit
        $grades = SpmbGrade::where('spmb_unit_id', $selectedUnitId)->orderBy('id', 'asc')->get();

        // Extract Sub-Units if this unit has grades with sub_unit
        $subUnits = $grades->whereNotNull('sub_unit')->pluck('sub_unit')->unique()->values();
        $subUnitData = $subUnits->map(function($su) use ($grades) {
            $suGrades = $grades->where('sub_unit', $su);
            $activeCount = $suGrades->where('is_active', true)->count();
            $totalCount = $suGrades->count();
            return (object) [
                'name' => $su,
                'is_active' => $activeCount > 0,
                'active_count' => $activeCount,
                'total_count' => $totalCount,
                'grade_ids' => $suGrades->pluck('id')->toArray(),
            ];
        });

        // Load Fee Categories & Fee items for this specific unit
        $feeCategories = \App\Models\SpmbFeeCategory::with(['fees' => function($q) use ($selectedUnitId) {
            $q->where('spmb_unit_id', $selectedUnitId)->with('unit');
        }])->get();

        $gateways = \App\Models\PaymentGateway::where('is_active', true)->with('paymentChannels')->get();

        return view('admin.settings-registration', compact(
            'periods', 'waves', 'types', 'feeCategories',
            'units', 'selectedUnit', 'selectedUnitId', 'isSuperAdmin',
            'grades', 'classPrograms', 'extraServices', 'gateways', 'subUnitData'
        ));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $selectedUnitId = $isSuperAdmin 
            ? $request->input('unit_id', SpmbUnit::value('id')) 
            : ($user->spmb_unit_id ?: SpmbUnit::value('id'));

        $selectedUnit = SpmbUnit::findOrFail($selectedUnitId);

        $activePeriodIds = $request->input('active_periods', []);
        $activeWaveIds = $request->input('active_waves', []);
        $activeTypeIds = $request->input('active_types', []);
        $activeProgramIds = $request->input('active_programs', []);
        $activeServiceIds = $request->input('active_services', []);
        $activeGradeIds = $request->input('active_grades', []);
        $activeFeeIds = $request->input('active_fees', []);
        $activeChannelIds = $request->input('active_channels', []);
        $unitIsActive = $request->has('unit_is_active');

        // Validation: Ensure at least one item of each core type is active for this unit
        if (empty($activePeriodIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Periode Akademik yang aktif untuk unit ' . $selectedUnit->name);
        }
        if (empty($activeWaveIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Gelombang Pendaftaran yang aktif untuk unit ' . $selectedUnit->name);
        }
        if (empty($activeTypeIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Jenis Pendaftaran yang aktif untuk unit ' . $selectedUnit->name);
        }
        if (empty($activeProgramIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Kategori Murid yang aktif untuk unit ' . $selectedUnit->name);
        }
        if (empty($activeGradeIds)) {
            return redirect()->back()->with('error', 'Gagal menyimpan: Minimal harus ada 1 Tingkatan Kelas yang aktif untuk unit ' . $selectedUnit->name);
        }

        DB::beginTransaction();
        try {
            $now = now();

            // 1. Update Pivot: Periods for this unit
            $allPeriodIds = SpmbPeriod::pluck('id')->toArray();
            foreach ($allPeriodIds as $pId) {
                DB::table('spmb_period_unit')->updateOrInsert(
                    ['spmb_period_id' => $pId, 'spmb_unit_id' => $selectedUnitId],
                    ['is_active' => in_array($pId, $activePeriodIds), 'updated_at' => $now]
                );
            }
            // Keep at least one global active period if matched
            if (!empty($activePeriodIds)) {
                SpmbPeriod::whereIn('id', $activePeriodIds)->update(['is_active' => true]);
            }

            // 2. Update Pivot: Waves for this unit
            $allWaveIds = SpmbWave::pluck('id')->toArray();
            foreach ($allWaveIds as $wId) {
                DB::table('spmb_wave_unit')->updateOrInsert(
                    ['spmb_wave_id' => $wId, 'spmb_unit_id' => $selectedUnitId],
                    ['is_active' => in_array($wId, $activeWaveIds), 'updated_at' => $now]
                );
            }
            if (!empty($activeWaveIds)) {
                SpmbWave::whereIn('id', $activeWaveIds)->update(['is_active' => true]);
            }

            // 3. Update Pivot: Types for this unit
            $allTypeIds = SpmbType::pluck('id')->toArray();
            foreach ($allTypeIds as $tId) {
                DB::table('spmb_type_unit')->updateOrInsert(
                    ['spmb_type_id' => $tId, 'spmb_unit_id' => $selectedUnitId],
                    ['is_active' => in_array($tId, $activeTypeIds), 'updated_at' => $now]
                );
            }
            if (!empty($activeTypeIds)) {
                SpmbType::whereIn('id', $activeTypeIds)->update(['is_active' => true]);
            }

            // 4. Update Pivot: Class Programs for this unit
            $allProgramIds = SpmbClassProgram::pluck('id')->toArray();
            foreach ($allProgramIds as $prId) {
                DB::table('spmb_class_program_unit')->updateOrInsert(
                    ['spmb_class_program_id' => $prId, 'spmb_unit_id' => $selectedUnitId],
                    ['is_active' => in_array($prId, $activeProgramIds), 'updated_at' => $now]
                );
            }
            if (!empty($activeProgramIds)) {
                SpmbClassProgram::whereIn('id', $activeProgramIds)->update(['is_active' => true]);
            }

            // 5. Update Pivot: Extra Services for this unit
            $allServiceIds = SpmbExtraService::pluck('id')->toArray();
            foreach ($allServiceIds as $sId) {
                DB::table('spmb_extra_service_unit')->updateOrInsert(
                    ['spmb_extra_service_id' => $sId, 'spmb_unit_id' => $selectedUnitId],
                    ['is_active' => in_array($sId, $activeServiceIds), 'updated_at' => $now]
                );
            }

            // 6. Update Unit active status
            $selectedUnit->update(['is_active' => $unitIsActive]);

            // 7. Update Grades for this unit
            SpmbGrade::where('spmb_unit_id', $selectedUnitId)->update(['is_active' => false]);
            if (!empty($activeGradeIds)) {
                SpmbGrade::where('spmb_unit_id', $selectedUnitId)->whereIn('id', $activeGradeIds)->update(['is_active' => true]);
            }

            // 8. Update Fees for this unit (only if explicitly submitted)
            if ($request->has('active_fees')) {
                SpmbFee::where('spmb_unit_id', $selectedUnitId)->update(['is_active' => false]);
                if (!empty($activeFeeIds)) {
                    SpmbFee::where('spmb_unit_id', $selectedUnitId)->whereIn('id', $activeFeeIds)->update(['is_active' => true]);
                }
            }

            // Sync fee snapshots for unpaid candidates in this unit
            \App\Http\Controllers\Web\SpmbFeesController::syncUnpaidRegistrationsFeeSnapshot([$selectedUnitId]);

            // 9. Payment Channels (if submitted)
            if ($request->has('has_channel_config')) {
                \App\Models\SpmbPaymentChannel::query()->update(['is_active' => false]);
                if (!empty($activeChannelIds)) {
                    \App\Models\SpmbPaymentChannel::whereIn('id', $activeChannelIds)->update(['is_active' => true]);
                }
            }

            // Log activity
            SpmbActivityLog::log(
                'UPDATE_ACTIVATION_SETTINGS',
                "Memperbarui konfigurasi aktivasi SPMB untuk unit {$selectedUnit->name} ({$selectedUnit->code}) oleh {$user->name}"
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }

        $activeTab = $request->input('active_tab', 'jalur_gelombang');
        return redirect()->route('admin.spmb-settings.registration', [
            'unit_id' => $selectedUnitId,
            'tab' => $activeTab
        ])->with('success', "Konfigurasi aktivasi SPMB untuk unit {$selectedUnit->name} berhasil diperbarui.");
    }
}
