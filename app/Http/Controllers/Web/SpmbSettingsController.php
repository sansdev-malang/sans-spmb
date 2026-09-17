<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbType;
use App\Models\SpmbUnit;
use App\Models\SpmbGrade;
use App\Models\SpmbClassProgram;
use App\Models\SpmbExtraService;
use App\Models\Registration;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SpmbSettingsController extends Controller
{
    public function index()
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $units = SpmbUnit::orderBy('id', 'asc')->get();
        $selectedUnitId = request()->get('unit_id', '');

        if (!$isSuperAdmin && auth()->user()->spmb_unit_id) {
            $selectedUnitId = (string) auth()->user()->spmb_unit_id;
        }

        $userUnitId = (!empty($selectedUnitId)) ? (int)$selectedUnitId : null;
        $defaultPeriodId = SpmbPeriod::getDefaultPeriodId($userUnitId);

        // Preload unit pivot active lookups for fast mapping
        $periodUnitPivots = \Illuminate\Support\Facades\DB::table('spmb_period_unit')
            ->where('is_active', true)
            ->get()
            ->groupBy('spmb_period_id');

        $waveUnitPivots = \Illuminate\Support\Facades\DB::table('spmb_wave_unit')
            ->where('is_active', true)
            ->get()
            ->groupBy('spmb_wave_id');

        $typeUnitPivots = \Illuminate\Support\Facades\DB::table('spmb_type_unit')
            ->where('is_active', true)
            ->get()
            ->groupBy('spmb_type_id');

        $programUnitPivots = \Illuminate\Support\Facades\DB::table('spmb_class_program_unit')
            ->where('is_active', true)
            ->get()
            ->groupBy('spmb_class_program_id');

        $periods = SpmbPeriod::orderBy('year', 'desc')->get()->map(function ($period) use ($defaultPeriodId, $periodUnitPivots, $selectedUnitId, $units) {
            $regCountQuery = Registration::where('spmb_period_id', $period->id);
            if (!empty($selectedUnitId)) {
                $regCountQuery->where('spmb_unit_id', $selectedUnitId);
            }
            $period->registrations_count = $regCountQuery->count();
            $period->is_current_default = ($period->id == $defaultPeriodId);

            $activeUnitIds = isset($periodUnitPivots[$period->id]) ? $periodUnitPivots[$period->id]->pluck('spmb_unit_id')->toArray() : [];
            $period->active_units = $units->whereIn('id', $activeUnitIds);
            $period->is_active_for_selected = !empty($selectedUnitId)
                ? in_array((int)$selectedUnitId, $activeUnitIds, true)
                : (!empty($activeUnitIds) || (bool)$period->is_active);

            return $period;
        });

        $waves = SpmbWave::all()->map(function ($wave) use ($waveUnitPivots, $selectedUnitId, $units) {
            $regCountQuery = Registration::where('spmb_wave_id', $wave->id);
            if (!empty($selectedUnitId)) {
                $regCountQuery->where('spmb_unit_id', $selectedUnitId);
            }
            $wave->registrations_count = $regCountQuery->count();

            $activeUnitIds = isset($waveUnitPivots[$wave->id]) ? $waveUnitPivots[$wave->id]->pluck('spmb_unit_id')->toArray() : [];
            $wave->active_units = $units->whereIn('id', $activeUnitIds);
            $wave->is_active_for_selected = !empty($selectedUnitId)
                ? in_array((int)$selectedUnitId, $activeUnitIds, true)
                : (!empty($activeUnitIds) || (bool)$wave->is_active);

            return $wave;
        });

        $types = SpmbType::all()->map(function ($type) use ($typeUnitPivots, $selectedUnitId, $units) {
            $regCountQuery = Registration::where('spmb_type_id', $type->id);
            if (!empty($selectedUnitId)) {
                $regCountQuery->where('spmb_unit_id', $selectedUnitId);
            }
            $type->registrations_count = $regCountQuery->count();

            $activeUnitIds = isset($typeUnitPivots[$type->id]) ? $typeUnitPivots[$type->id]->pluck('spmb_unit_id')->toArray() : [];
            $type->active_units = $units->whereIn('id', $activeUnitIds);
            $type->is_active_for_selected = !empty($selectedUnitId)
                ? in_array((int)$selectedUnitId, $activeUnitIds, true)
                : (!empty($activeUnitIds) || (bool)$type->is_active);

            return $type;
        });

        $classPrograms = SpmbClassProgram::all()->map(function ($program) use ($programUnitPivots, $selectedUnitId, $units) {
            $regCountQuery = Registration::where('spmb_class_program_id', $program->id);
            if (!empty($selectedUnitId)) {
                $regCountQuery->where('spmb_unit_id', $selectedUnitId);
            }
            $program->registrations_count = $regCountQuery->count();

            $activeUnitIds = isset($programUnitPivots[$program->id]) ? $programUnitPivots[$program->id]->pluck('spmb_unit_id')->toArray() : [];
            $program->active_units = $units->whereIn('id', $activeUnitIds);
            $program->is_active_for_selected = !empty($selectedUnitId)
                ? in_array((int)$selectedUnitId, $activeUnitIds, true)
                : (!empty($activeUnitIds) || (bool)$program->is_active);

            return $program;
        });

        $activeTab = request()->input('tab', 'periode');
        return view('admin.settings-spmb', compact('periods', 'waves', 'types', 'classPrograms', 'activeTab', 'defaultPeriodId', 'units', 'selectedUnitId', 'isSuperAdmin'));
    }

    public function unitsGrades()
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $userUnitId = auth()->user()->spmb_unit_id;

        if (!$isSuperAdmin && $userUnitId) {
            $units = SpmbUnit::where('id', $userUnitId)->get()->map(function ($unit) {
                $unit->registrations_count = Registration::where('spmb_unit_id', $unit->id)->count();
                return $unit;
            });

            $grades = SpmbGrade::with('unit')->where('spmb_unit_id', $userUnitId)->get()->map(function ($grade) {
                $grade->registrations_count = Registration::where('spmb_grade_id', $grade->id)->count();
                return $grade;
            });

            $extraServices = SpmbExtraService::with('unit')
                ->where(function($q) use ($userUnitId) {
                    $q->where('spmb_unit_id', $userUnitId)
                      ->orWhereNull('spmb_unit_id');
                })
                ->get()
                ->map(function ($service) {
                    $service->registrations_count = $service->registrations()->count();
                    return $service;
                });

            $selectedUnitId = (string)$userUnitId;
        } else {
            $units = SpmbUnit::all()->map(function ($unit) {
                $unit->registrations_count = Registration::where('spmb_unit_id', $unit->id)->count();
                return $unit;
            });

            $grades = SpmbGrade::with('unit')->get()->map(function ($grade) {
                $grade->registrations_count = Registration::where('spmb_grade_id', $grade->id)->count();
                return $grade;
            });

            $extraServices = SpmbExtraService::with('unit')->get()->map(function ($service) {
                $service->registrations_count = $service->registrations()->count();
                return $service;
            });

            $selectedUnitId = request()->get('unit_id', '');
        }

        $activeTab = request()->get('tab', 'unit');
        $types = SpmbType::where('is_active', true)->get();
        $classPrograms = SpmbClassProgram::where('is_active', true)->get();
        $waves = SpmbWave::where('is_active', true)->get();
        $periods = SpmbPeriod::orderBy('year', 'desc')->get();

        return view('admin.settings-spmb-units', compact('units', 'grades', 'extraServices', 'activeTab', 'selectedUnitId', 'isSuperAdmin', 'types', 'classPrograms', 'waves', 'periods'));
    }

    public function qrcode()
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $qrcodeUrl = Setting::get('spmb_qrcode_url', url('/register'));
        $schoolLogo = Setting::get('school_logo_url') ?: Setting::get('school_logo');
        $logoUrl = null;
        if ($schoolLogo && (filter_var($schoolLogo, FILTER_VALIDATE_URL) || str_starts_with($schoolLogo, 'http'))) {
            $logoUrl = $schoolLogo;
        } elseif ($schoolLogo && file_exists(public_path(ltrim($schoolLogo, '/')))) {
            $logoUrl = asset(ltrim($schoolLogo, '/'));
        } elseif ($schoolLogo && file_exists(public_path('storage/' . ltrim($schoolLogo, '/')))) {
            $logoUrl = asset('storage/' . ltrim($schoolLogo, '/'));
        } else {
            $logoUrl = file_exists(public_path('logo/paud.png')) ? asset('logo/paud.png') : null;
        }

        return view('admin.settings-spmb-qrcode', compact('qrcodeUrl', 'isSuperAdmin', 'logoUrl'));
    }

    public function saveQrcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qrcode_url' => 'required|url'
        ], [
            'qrcode_url.url' => 'Tautan QR Code harus berupa alamat URL yang valid (menggunakan http:// atau https://).'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Setting::set('spmb_qrcode_url', $request->qrcode_url);

        return redirect()->back()->with('success', 'Tautan QR Code berhasil disimpan.');
    }

    // Period CRUD
    public function storePeriod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|string|regex:/^[0-9]{4}-[0-9]{4}$/|unique:spmb_periods,year'
        ], [
            'year.regex' => 'Format periode akademik harus YYYY-YYYY (contoh: 2024-2025).'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'periode_create');
        }

        SpmbPeriod::create(['year' => $request->year]);

        return redirect()->route('admin.spmb-settings', ['tab' => 'periode'])->with('success', 'Periode akademik berhasil ditambahkan.');
    }

    public function updatePeriod(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|string|regex:/^[0-9]{4}-[0-9]{4}$/|unique:spmb_periods,year,' . $id
        ], [
            'year.regex' => 'Format periode akademik harus YYYY-YYYY (contoh: 2024-2025).'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'periode_edit_' . $id);
        }

        $period = SpmbPeriod::findOrFail($id);

        $period->update(['year' => $request->year]);
        return redirect()->route('admin.spmb-settings', ['tab' => 'periode'])->with('success', 'Periode akademik berhasil diperbarui.');
    }

    public function setDefaultPeriod(Request $request, $id)
    {
        $period = SpmbPeriod::findOrFail($id);
        $unitId = (!auth()->user()->isSuperAdmin()) ? auth()->user()->spmb_unit_id : $request->get('unit_id', null);

        SpmbPeriod::setDefaultPeriod($period->id, $unitId);

        return redirect()->route('admin.spmb-settings', ['tab' => 'periode'])->with('success', 'Tahun Pelajaran ' . $period->year . ' berhasil dijadikan sebagai Tahun Default Sistem.');
    }

    public function destroyPeriod($id)
    {
        $period = SpmbPeriod::findOrFail($id);

        if (Registration::where('spmb_period_id', $id)->exists()) {
            return redirect()->route('admin.spmb-settings', ['tab' => 'periode'])->with('error', 'Tidak dapat menghapus periode ini karena sudah memiliki transaksi pendaftaran aktif.');
        }

        $period->delete();
        return redirect()->route('admin.spmb-settings', ['tab' => 'periode'])->with('success', 'Periode akademik berhasil dihapus.');
    }

    // Wave CRUD
    public function storeWave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_waves,name',
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'gelombang_create');
        }

        SpmbWave::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->route('admin.spmb-settings', ['tab' => 'gelombang'])->with('success', 'Gelombang pendaftaran berhasil ditambahkan.');
    }

    public function updateWave(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_waves,name,' . $id,
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'gelombang_edit_' . $id);
        }

        $wave = SpmbWave::findOrFail($id);

        $wave->update([
            'name' => $request->name,
            'description' => $request->description
        ]);
        return redirect()->route('admin.spmb-settings', ['tab' => 'gelombang'])->with('success', 'Gelombang pendaftaran berhasil diperbarui.');
    }

    public function destroyWave($id)
    {
        $wave = SpmbWave::findOrFail($id);

        if (Registration::where('spmb_wave_id', $id)->exists()) {
            return redirect()->route('admin.spmb-settings', ['tab' => 'gelombang'])->with('error', 'Tidak dapat menghapus gelombang ini karena sudah digunakan dalam transaksi.');
        }

        $wave->delete();
        return redirect()->route('admin.spmb-settings', ['tab' => 'gelombang'])->with('success', 'Gelombang pendaftaran berhasil dihapus.');
    }

    // Type CRUD
    public function storeType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_types,name',
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_create');
        }

        SpmbType::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->route('admin.spmb-settings', ['tab' => 'jenis'])->with('success', 'Jenis pendaftaran berhasil ditambahkan.');
    }

    public function updateType(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_types,name,' . $id,
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_edit_' . $id);
        }

        $type = SpmbType::findOrFail($id);

        $type->update([
            'name' => $request->name,
            'description' => $request->description
        ]);
        return redirect()->route('admin.spmb-settings', ['tab' => 'jenis'])->with('success', 'Jenis pendaftaran berhasil diperbarui.');
    }

    public function destroyType($id)
    {
        $type = SpmbType::findOrFail($id);
        if (Registration::where('spmb_type_id', $type->id)->exists()) {
            return redirect()->route('admin.spmb-settings', ['tab' => 'jenis'])->with('error', 'Gagal menghapus! Jalur ini sedang digunakan oleh pendaftar.');
        }
        $type->delete();
        return redirect()->route('admin.spmb-settings', ['tab' => 'jenis'])->with('success', 'Jalur pendaftaran berhasil dihapus.');
    }

    // Unit CRUD
    public function storeUnit(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat menambahkan unit baru.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:30',
            'admin_contact_name' => 'nullable|string|max:100',
            'spmb_group_url' => 'nullable|url|max:500',
            'is_active' => 'boolean'
        ], [
            'spmb_group_url.url' => 'Tautan Group WhatsApp harus berupa format URL yang valid (menggunakan https:// atau http://).'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'unit'])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'unit_create');
        }

        SpmbUnit::create($request->all());
        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'unit'])->with('success', 'Unit berhasil ditambahkan.');
    }

    public function updateUnit(Request $request, $id)
    {
        if (!auth()->user()->isSuperAdmin() && $id != auth()->user()->spmb_unit_id) {
            abort(403, 'Akses tidak diizinkan untuk unit ini.');
        }

        $unit = SpmbUnit::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:30',
            'admin_contact_name' => 'nullable|string|max:100',
            'spmb_group_url' => 'nullable|url|max:500',
            'is_active' => 'boolean'
        ], [
            'spmb_group_url.url' => 'Tautan Group WhatsApp harus berupa format URL yang valid (menggunakan https:// atau http://).'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'unit'])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'unit_edit_' . $id);
        }
        
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        if (!empty($unit->code)) {
            $data['code'] = $unit->code; // Preserve existing unit code
        }
        $unit->update($data);

        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'unit'])->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroyUnit($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat menghapus unit.');
        }

        $unit = SpmbUnit::findOrFail($id);
        if (Registration::where('spmb_unit_id', $unit->id)->exists()) {
            return redirect()->back()->with('error', 'Gagal menghapus! Unit sedang digunakan oleh pendaftar.');
        }
        $unit->delete();
        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'unit'])->with('success', 'Unit berhasil dihapus.');
    }

    // Grade CRUD
    public function storeGrade(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            $request->merge(['spmb_unit_id' => auth()->user()->spmb_unit_id]);
        }

        $validator = Validator::make($request->all(), [
            'spmb_unit_id' => 'required|exists:spmb_units,id',
            'sub_unit' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'min_age_years' => 'nullable|integer|min:0|max:30',
            'min_age_months' => 'nullable|integer|min:0|max:11',
            'max_age_years' => 'nullable|integer|min:0|max:30',
            'max_age_months' => 'nullable|integer|min:0|max:11',
            'age_notes' => 'nullable|string|max:255',
            'applicable_types' => 'nullable|array',
            'applicable_types.*' => 'integer',
            'applicable_class_programs' => 'nullable|array',
            'applicable_class_programs.*' => 'integer',
            'applicable_waves' => 'nullable|array',
            'applicable_waves.*' => 'integer',
            'applicable_periods' => 'nullable|array',
            'applicable_periods.*' => 'integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'grade'])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'grade_create');
        }

        $data = $request->all();
        $data['sub_unit'] = $request->filled('sub_unit') ? trim($request->sub_unit) : null;
        $data['is_active'] = $request->has('is_active');
        $data['min_age_months'] = $request->input('min_age_months', 0) ?: 0;
        $data['max_age_months'] = $request->input('max_age_months', 0) ?: 0;
        
        $totalTypes = SpmbType::where('is_active', true)->count();
        $totalProgs = SpmbClassProgram::where('is_active', true)->count();
        $totalWaves = SpmbWave::where('is_active', true)->count();
        $totalPeriods = SpmbPeriod::where('is_active', true)->count();

        $reqTypes = !empty($request->applicable_types) ? array_map('intval', (array)$request->applicable_types) : null;
        $reqProgs = !empty($request->applicable_class_programs) ? array_map('intval', (array)$request->applicable_class_programs) : null;
        $reqWaves = !empty($request->applicable_waves) ? array_map('intval', (array)$request->applicable_waves) : null;
        $reqPeriods = !empty($request->applicable_periods) ? array_map('intval', (array)$request->applicable_periods) : null;

        $data['applicable_types'] = ($reqTypes && count($reqTypes) < $totalTypes) ? $reqTypes : null;
        $data['applicable_class_programs'] = ($reqProgs && count($reqProgs) < $totalProgs) ? $reqProgs : null;
        $data['applicable_waves'] = ($reqWaves && count($reqWaves) < $totalWaves) ? $reqWaves : null;
        $data['applicable_periods'] = ($reqPeriods && count($reqPeriods) < $totalPeriods) ? $reqPeriods : null;
        
        SpmbGrade::create($data);
        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'grade'])->with('success', 'Tingkatan berhasil ditambahkan.');
    }

    public function updateGrade(Request $request, $id)
    {
        $grade = SpmbGrade::findOrFail($id);

        if (!auth()->user()->isSuperAdmin()) {
            if ($grade->spmb_unit_id != auth()->user()->spmb_unit_id) {
                abort(403, 'Akses tidak diizinkan untuk tingkatan unit ini.');
            }
            $request->merge(['spmb_unit_id' => auth()->user()->spmb_unit_id]);
        }

        $validator = Validator::make($request->all(), [
            'spmb_unit_id' => 'required|exists:spmb_units,id',
            'sub_unit' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'min_age_years' => 'nullable|integer|min:0|max:30',
            'min_age_months' => 'nullable|integer|min:0|max:11',
            'max_age_years' => 'nullable|integer|min:0|max:30',
            'max_age_months' => 'nullable|integer|min:0|max:11',
            'age_notes' => 'nullable|string|max:255',
            'applicable_types' => 'nullable|array',
            'applicable_types.*' => 'integer',
            'applicable_class_programs' => 'nullable|array',
            'applicable_class_programs.*' => 'integer',
            'applicable_waves' => 'nullable|array',
            'applicable_waves.*' => 'integer',
            'applicable_periods' => 'nullable|array',
            'applicable_periods.*' => 'integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'grade'])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'grade_edit_' . $id);
        }
        
        $data = $request->all();
        $data['sub_unit'] = $request->filled('sub_unit') ? trim($request->sub_unit) : null;
        $data['is_active'] = $request->has('is_active');
        $data['min_age_months'] = $request->input('min_age_months', 0) ?: 0;
        $data['max_age_months'] = $request->input('max_age_months', 0) ?: 0;
        
        $totalTypes = SpmbType::where('is_active', true)->count();
        $totalProgs = SpmbClassProgram::where('is_active', true)->count();
        $totalWaves = SpmbWave::where('is_active', true)->count();
        $totalPeriods = SpmbPeriod::where('is_active', true)->count();

        $reqTypes = !empty($request->applicable_types) ? array_map('intval', (array)$request->applicable_types) : null;
        $reqProgs = !empty($request->applicable_class_programs) ? array_map('intval', (array)$request->applicable_class_programs) : null;
        $reqWaves = !empty($request->applicable_waves) ? array_map('intval', (array)$request->applicable_waves) : null;
        $reqPeriods = !empty($request->applicable_periods) ? array_map('intval', (array)$request->applicable_periods) : null;

        $data['applicable_types'] = ($reqTypes && count($reqTypes) < $totalTypes) ? $reqTypes : null;
        $data['applicable_class_programs'] = ($reqProgs && count($reqProgs) < $totalProgs) ? $reqProgs : null;
        $data['applicable_waves'] = ($reqWaves && count($reqWaves) < $totalWaves) ? $reqWaves : null;
        $data['applicable_periods'] = ($reqPeriods && count($reqPeriods) < $totalPeriods) ? $reqPeriods : null;
        
        $grade->update($data);

        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'grade'])->with('success', 'Tingkatan berhasil diperbarui.');
    }

    public function destroyGrade($id)
    {
        $grade = SpmbGrade::findOrFail($id);

        if (!auth()->user()->isSuperAdmin() && $grade->spmb_unit_id != auth()->user()->spmb_unit_id) {
            abort(403, 'Akses tidak diizinkan untuk menghapus tingkatan unit ini.');
        }

        if (Registration::where('spmb_grade_id', $grade->id)->exists()) {
            return redirect()->back()->with('error', 'Gagal menghapus! Tingkatan sedang digunakan oleh pendaftar.');
        }
        $grade->delete();
        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'grade'])->with('success', 'Tingkatan berhasil dihapus.');
    }

    // Class Program CRUD
    public function storeClassProgram(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:spmb_class_programs,name',
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'program_create');
        }

        SpmbClassProgram::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true
        ]);

        return redirect()->route('admin.spmb-settings', ['tab' => 'program'])->with('success', 'Kategori murid berhasil ditambahkan.');
    }

    public function updateClassProgram(Request $request, $id)
    {
        $program = SpmbClassProgram::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:spmb_class_programs,name,' . $id,
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'program_edit_' . $id);
        }

        $program->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.spmb-settings', ['tab' => 'program'])->with('success', 'Kategori murid berhasil diperbarui.');
    }

    public function destroyClassProgram($id)
    {
        $program = SpmbClassProgram::findOrFail($id);

        if (Registration::where('spmb_class_program_id', $program->id)->exists()) {
            return redirect()->route('admin.spmb-settings', ['tab' => 'program'])->with('error', 'Gagal menghapus! Kategori murid sedang digunakan oleh pendaftar.');
        }

        $program->delete();
        return redirect()->route('admin.spmb-settings', ['tab' => 'program'])->with('success', 'Kategori murid berhasil dihapus.');
    }

    // Extra Services CRUD
    public function storeExtraService(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            $request->merge(['spmb_unit_id' => auth()->user()->spmb_unit_id]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:spmb_extra_services,code',
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
            'applicable_types' => 'nullable|array',
            'applicable_types.*' => 'integer',
            'applicable_class_programs' => 'nullable|array',
            'applicable_class_programs.*' => 'integer',
            'applicable_waves' => 'nullable|array',
            'applicable_waves.*' => 'integer',
            'applicable_periods' => 'nullable|array',
            'applicable_periods.*' => 'integer',
            'applicable_grades' => 'nullable|array',
            'applicable_grades.*' => 'integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'extra'])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'extra_create');
        }

        $data = $request->all();
        $data['spmb_unit_id'] = $request->filled('spmb_unit_id') ? $request->spmb_unit_id : null;
        $data['is_active'] = $request->has('is_active') || $request->input('is_active') == '1';

        $totalTypes = SpmbType::where('is_active', true)->count();
        $totalProgs = SpmbClassProgram::where('is_active', true)->count();
        $totalWaves = SpmbWave::where('is_active', true)->count();
        $totalPeriods = SpmbPeriod::where('is_active', true)->count();
        $totalGrades = SpmbGrade::where('is_active', true)->count();

        $reqTypes = !empty($request->applicable_types) ? array_map('intval', (array)$request->applicable_types) : null;
        $reqProgs = !empty($request->applicable_class_programs) ? array_map('intval', (array)$request->applicable_class_programs) : null;
        $reqWaves = !empty($request->applicable_waves) ? array_map('intval', (array)$request->applicable_waves) : null;
        $reqPeriods = !empty($request->applicable_periods) ? array_map('intval', (array)$request->applicable_periods) : null;
        $reqGrades = !empty($request->applicable_grades) ? array_map('intval', (array)$request->applicable_grades) : null;

        $data['applicable_types'] = ($reqTypes && count($reqTypes) < $totalTypes) ? $reqTypes : null;
        $data['applicable_class_programs'] = ($reqProgs && count($reqProgs) < $totalProgs) ? $reqProgs : null;
        $data['applicable_waves'] = ($reqWaves && count($reqWaves) < $totalWaves) ? $reqWaves : null;
        $data['applicable_periods'] = ($reqPeriods && count($reqPeriods) < $totalPeriods) ? $reqPeriods : null;
        $data['applicable_grades'] = ($reqGrades && count($reqGrades) < $totalGrades) ? $reqGrades : null;

        SpmbExtraService::create($data);
        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'extra'])->with('success', 'Layanan tambahan berhasil ditambahkan.');
    }

    public function updateExtraService(Request $request, $id)
    {
        $service = SpmbExtraService::findOrFail($id);

        if (!auth()->user()->isSuperAdmin()) {
            if ($service->spmb_unit_id && $service->spmb_unit_id != auth()->user()->spmb_unit_id) {
                abort(403, 'Akses tidak diizinkan untuk layanan unit ini.');
            }
            $request->merge(['spmb_unit_id' => auth()->user()->spmb_unit_id]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:spmb_extra_services,code,' . $id,
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
            'applicable_types' => 'nullable|array',
            'applicable_types.*' => 'integer',
            'applicable_class_programs' => 'nullable|array',
            'applicable_class_programs.*' => 'integer',
            'applicable_waves' => 'nullable|array',
            'applicable_waves.*' => 'integer',
            'applicable_periods' => 'nullable|array',
            'applicable_periods.*' => 'integer',
            'applicable_grades' => 'nullable|array',
            'applicable_grades.*' => 'integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'extra'])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'extra_edit_' . $id);
        }

        $data = $request->all();
        $data['spmb_unit_id'] = $request->filled('spmb_unit_id') ? $request->spmb_unit_id : null;
        $data['is_active'] = $request->has('is_active') || $request->input('is_active') == '1';

        $totalTypes = SpmbType::where('is_active', true)->count();
        $totalProgs = SpmbClassProgram::where('is_active', true)->count();
        $totalWaves = SpmbWave::where('is_active', true)->count();
        $totalPeriods = SpmbPeriod::where('is_active', true)->count();
        $totalGrades = SpmbGrade::where('is_active', true)->count();

        $reqTypes = !empty($request->applicable_types) ? array_map('intval', (array)$request->applicable_types) : null;
        $reqProgs = !empty($request->applicable_class_programs) ? array_map('intval', (array)$request->applicable_class_programs) : null;
        $reqWaves = !empty($request->applicable_waves) ? array_map('intval', (array)$request->applicable_waves) : null;
        $reqPeriods = !empty($request->applicable_periods) ? array_map('intval', (array)$request->applicable_periods) : null;
        $reqGrades = !empty($request->applicable_grades) ? array_map('intval', (array)$request->applicable_grades) : null;

        $data['applicable_types'] = ($reqTypes && count($reqTypes) < $totalTypes) ? $reqTypes : null;
        $data['applicable_class_programs'] = ($reqProgs && count($reqProgs) < $totalProgs) ? $reqProgs : null;
        $data['applicable_waves'] = ($reqWaves && count($reqWaves) < $totalWaves) ? $reqWaves : null;
        $data['applicable_periods'] = ($reqPeriods && count($reqPeriods) < $totalPeriods) ? $reqPeriods : null;
        $data['applicable_grades'] = ($reqGrades && count($reqGrades) < $totalGrades) ? $reqGrades : null;

        $service->update($data);

        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'extra'])->with('success', 'Layanan tambahan berhasil diperbarui.');
    }

    public function destroyExtraService($id)
    {
        $service = SpmbExtraService::findOrFail($id);

        if (!auth()->user()->isSuperAdmin() && $service->spmb_unit_id != auth()->user()->spmb_unit_id) {
            abort(403, 'Akses tidak diizinkan untuk menghapus layanan unit ini.');
        }

        if ($service->registrations()->exists()) {
            return redirect()->back()->with('error', 'Gagal menghapus! Layanan tambahan sedang digunakan oleh pendaftar.');
        }
        $service->delete();
        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'extra'])->with('success', 'Layanan tambahan berhasil dihapus.');
    }

    public function customerService()
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        if (!$isSuperAdmin) {
            $units = SpmbUnit::where('id', auth()->user()->spmb_unit_id)->get();
        } else {
            $units = SpmbUnit::all();
        }

        $settings = [
            'spmb_cs_whatsapp' => Setting::get('spmb_cs_whatsapp', '081234567890'),
            'spmb_cs_name' => Setting::get('spmb_cs_name', 'Customer Service SPMB'),
            'spmb_cs_hours' => Setting::get('spmb_cs_hours', 'Senin - Jumat, 08:00 - 15:00 WIB'),
            'spmb_cs_card_title' => Setting::get('spmb_cs_card_title', 'Pusat Bantuan & Konsultasi SPMB'),
            'spmb_cs_card_desc' => Setting::get('spmb_cs_card_desc', 'Ada pertanyaan seputar persyaratan atau alur masuk? Tim panitia siap melayani Anda.'),
            'spmb_cs_message' => Setting::get('spmb_cs_message', 'Halo Panitia SPMB Sekolah Anak Saleh, saya ingin berkonsultasi mengenai pendaftaran murid baru.'),
        ];

        return view('admin.settings-spmb-cs', compact('units', 'settings', 'isSuperAdmin'));
    }

    public function saveCustomerService(Request $request)
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        if ($isSuperAdmin) {
            $request->validate([
                'spmb_cs_whatsapp' => 'required|string|max:30',
                'spmb_cs_name' => 'nullable|string|max:100',
                'spmb_cs_hours' => 'nullable|string|max:100',
                'spmb_cs_card_title' => 'nullable|string|max:255',
                'spmb_cs_card_desc' => 'nullable|string|max:500',
                'spmb_cs_message' => 'nullable|string|max:1000',
            ]);

            Setting::set('spmb_cs_whatsapp', $request->spmb_cs_whatsapp);
            Setting::set('spmb_cs_name', $request->spmb_cs_name ?: 'Customer Service SPMB');
            Setting::set('spmb_cs_hours', $request->spmb_cs_hours ?: 'Senin - Jumat, 08:00 - 15:00 WIB');
            Setting::set('spmb_cs_card_title', $request->spmb_cs_card_title ?: 'Pusat Bantuan & Konsultasi SPMB');
            Setting::set('spmb_cs_card_desc', $request->spmb_cs_card_desc ?: 'Ada pertanyaan seputar persyaratan atau alur masuk? Tim panitia siap melayani Anda.');
            Setting::set('spmb_cs_message', $request->spmb_cs_message ?: 'Halo Panitia SPMB Sekolah Anak Saleh, saya ingin berkonsultasi mengenai pendaftaran murid baru.');
        }

        if ($request->has('units') && is_array($request->units)) {
            foreach ($request->units as $unitId => $unitData) {
                if (!$isSuperAdmin && $unitId != auth()->user()->spmb_unit_id) {
                    continue;
                }

                $unit = SpmbUnit::find($unitId);
                if ($unit) {
                    $unit->update([
                        'whatsapp_number' => $unitData['whatsapp_number'] ?? null,
                        'admin_contact_name' => $unitData['admin_contact_name'] ?? null,
                        'spmb_group_url' => $unitData['spmb_group_url'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.spmb-settings.cs')->with('success', 'Pengaturan Kontak Panitia berhasil disimpan.');
    }

    public function brochures()
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        if (!$isSuperAdmin) {
            $units = SpmbUnit::where('id', auth()->user()->spmb_unit_id)->get();
        } else {
            $units = SpmbUnit::all();
        }

        $brochures = [];
        foreach ($units as $unit) {
            $code = strtolower($unit->code);
            $brochures[$unit->id] = [
                'unit' => $unit,
                'code' => $code,
                'brochure_url' => Setting::get('unit_' . $code . '_brochure_url', ''),
                'attachment_url' => Setting::get('unit_' . $code . '_attachment_url', ''),
                'brochure_title' => Setting::get('unit_' . $code . '_brochure_title', 'Brosur ' . $unit->name),
                'brochure_desc' => Setting::get('unit_' . $code . '_brochure_desc', 'Informasi kurikulum, program unggulan, dan alur pendaftaran.'),
            ];
        }

        return view('admin.settings-spmb-brochures', compact('units', 'brochures', 'isSuperAdmin'));
    }

    public function saveBrochures(Request $request)
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        if (!$isSuperAdmin) {
            $units = SpmbUnit::where('id', auth()->user()->spmb_unit_id)->get();
        } else {
            $units = SpmbUnit::all();
        }
        
        foreach ($units as $unit) {
            $code = strtolower($unit->code);
            
            // Check delete brochure
            if ($request->input('delete_unit_' . $code . '_brochure') == '1') {
                Setting::set('unit_' . $code . '_brochure_url', '');
            }
            
            // Check upload new brochure
            if ($request->hasFile('unit_' . $code . '_brochure_file')) {
                $file = $request->file('unit_' . $code . '_brochure_file');
                $path = $file->store('documents', 'public');
                Setting::set('unit_' . $code . '_brochure_url', Storage::url($path));
            } elseif ($request->filled('unit_' . $code . '_brochure_url_custom')) {
                Setting::set('unit_' . $code . '_brochure_url', $request->input('unit_' . $code . '_brochure_url_custom'));
            }

            // Check delete attachment
            if ($request->input('delete_unit_' . $code . '_attachment') == '1') {
                Setting::set('unit_' . $code . '_attachment_url', '');
            }

            // Check upload new attachment
            if ($request->hasFile('unit_' . $code . '_attachment_file')) {
                $file = $request->file('unit_' . $code . '_attachment_file');
                $path = $file->store('documents', 'public');
                Setting::set('unit_' . $code . '_attachment_url', Storage::url($path));
            } elseif ($request->filled('unit_' . $code . '_attachment_url_custom')) {
                Setting::set('unit_' . $code . '_attachment_url', $request->input('unit_' . $code . '_attachment_url_custom'));
            }

            // Save title & desc if present
            if ($request->has('unit_' . $code . '_brochure_title')) {
                Setting::set('unit_' . $code . '_brochure_title', $request->input('unit_' . $code . '_brochure_title'));
            }
            if ($request->has('unit_' . $code . '_brochure_desc')) {
                Setting::set('unit_' . $code . '_brochure_desc', $request->input('unit_' . $code . '_brochure_desc'));
            }
        }

        return redirect()->route('admin.spmb-settings.brochures')->with('success', 'Brosur dan dokumen unit sekolah berhasil diperbarui.');
    }
}
