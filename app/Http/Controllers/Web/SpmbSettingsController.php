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
        $periods = SpmbPeriod::all()->map(function ($period) {
            $period->registrations_count = Registration::where('spmb_period_id', $period->id)->count();
            return $period;
        });

        $waves = SpmbWave::all()->map(function ($wave) {
            $wave->registrations_count = Registration::where('spmb_wave_id', $wave->id)->count();
            return $wave;
        });

        $types = SpmbType::all()->map(function ($type) {
            $type->registrations_count = Registration::where('spmb_type_id', $type->id)->count();
            return $type;
        });

        $classPrograms = SpmbClassProgram::all()->map(function ($program) {
            $program->registrations_count = Registration::where('spmb_class_program_id', $program->id)->count();
            return $program;
        });

        $activeTab = request()->get('tab', 'periode');
        return view('admin.settings-spmb', compact('periods', 'waves', 'types', 'classPrograms', 'activeTab'));
    }

    public function unitsGrades()
    {
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

        return view('admin.settings-spmb-units', compact('units', 'grades', 'extraServices'));
    }

    public function qrcode()
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $qrcodeUrl = \App\Models\Setting::get('spmb_qrcode_url', url('/register'));
        return view('admin.settings-spmb-qrcode', compact('qrcodeUrl', 'isSuperAdmin'));
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

        \App\Models\Setting::set('spmb_qrcode_url', $request->qrcode_url);

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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:30',
            'admin_contact_name' => 'nullable|string|max:100',
            'is_active' => 'boolean'
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
        $unit = SpmbUnit::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:30',
            'admin_contact_name' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'unit'])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'unit_edit_' . $id);
        }
        
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $unit->update($data);

        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'unit'])->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroyUnit($id)
    {
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
        $validator = Validator::make($request->all(), [
            'spmb_unit_id' => 'required|exists:spmb_units,id',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'grade'])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'grade_create');
        }

        SpmbGrade::create($request->all());
        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'grade'])->with('success', 'Tingkatan berhasil ditambahkan.');
    }

    public function updateGrade(Request $request, $id)
    {
        $grade = SpmbGrade::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'spmb_unit_id' => 'required|exists:spmb_units,id',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'grade'])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'grade_edit_' . $id);
        }
        
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $grade->update($data);

        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'grade'])->with('success', 'Tingkatan berhasil diperbarui.');
    }

    public function destroyGrade($id)
    {
        $grade = SpmbGrade::findOrFail($id);
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

        return redirect()->route('admin.spmb-settings', ['tab' => 'program'])->with('success', 'Program kelas berhasil ditambahkan.');
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

        return redirect()->route('admin.spmb-settings', ['tab' => 'program'])->with('success', 'Program kelas berhasil diperbarui.');
    }

    public function destroyClassProgram($id)
    {
        $program = SpmbClassProgram::findOrFail($id);

        if (Registration::where('spmb_class_program_id', $program->id)->exists()) {
            return redirect()->route('admin.spmb-settings', ['tab' => 'program'])->with('error', 'Gagal menghapus! Program kelas sedang digunakan oleh pendaftar.');
        }

        $program->delete();
        return redirect()->route('admin.spmb-settings', ['tab' => 'program'])->with('success', 'Program kelas berhasil dihapus.');
    }

    // Extra Services CRUD
    public function storeExtraService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:spmb_extra_services,code',
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
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
        SpmbExtraService::create($data);
        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'extra'])->with('success', 'Layanan tambahan berhasil ditambahkan.');
    }

    public function updateExtraService(Request $request, $id)
    {
        $service = SpmbExtraService::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:spmb_extra_services,code,' . $id,
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
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
        $service->update($data);

        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'extra'])->with('success', 'Layanan tambahan berhasil diperbarui.');
    }

    public function destroyExtraService($id)
    {
        $service = SpmbExtraService::findOrFail($id);
        if ($service->registrations()->exists()) {
            return redirect()->back()->with('error', 'Gagal menghapus! Layanan tambahan sedang digunakan oleh pendaftar.');
        }
        $service->delete();
        return redirect()->route('admin.spmb-settings.units-grades', ['tab' => 'extra'])->with('success', 'Layanan tambahan berhasil dihapus.');
    }

    public function customerService()
    {
        $units = SpmbUnit::all();
        $settings = [
            'spmb_cs_whatsapp' => Setting::get('spmb_cs_whatsapp', '081234567890'),
            'spmb_cs_name' => Setting::get('spmb_cs_name', 'Customer Service SPMB'),
            'spmb_cs_hours' => Setting::get('spmb_cs_hours', 'Senin - Jumat, 08:00 - 15:00 WIB'),
            'spmb_cs_card_title' => Setting::get('spmb_cs_card_title', 'Pusat Bantuan & Konsultasi SPMB'),
            'spmb_cs_card_desc' => Setting::get('spmb_cs_card_desc', 'Ada pertanyaan seputar persyaratan atau alur masuk? Tim panitia siap melayani Anda.'),
            'spmb_cs_message' => Setting::get('spmb_cs_message', 'Halo Panitia SPMB Sekolah Anak Saleh, saya ingin berkonsultasi mengenai pendaftaran siswa baru.'),
        ];

        return view('admin.settings-spmb-cs', compact('units', 'settings'));
    }

    public function saveCustomerService(Request $request)
    {
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
        Setting::set('spmb_cs_message', $request->spmb_cs_message ?: 'Halo Panitia SPMB Sekolah Anak Saleh, saya ingin berkonsultasi mengenai pendaftaran siswa baru.');

        if ($request->has('units') && is_array($request->units)) {
            foreach ($request->units as $unitId => $unitData) {
                $unit = SpmbUnit::find($unitId);
                if ($unit) {
                    $unit->update([
                        'whatsapp_number' => $unitData['whatsapp_number'] ?? null,
                        'admin_contact_name' => $unitData['admin_contact_name'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.spmb-settings.cs')->with('success', 'Pengaturan Customer Service & Kontak Panitia berhasil disimpan.');
    }

    public function brochures()
    {
        $units = SpmbUnit::all();
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

        return view('admin.settings-spmb-brochures', compact('units', 'brochures'));
    }

    public function saveBrochures(Request $request)
    {
        $units = SpmbUnit::all();
        
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
