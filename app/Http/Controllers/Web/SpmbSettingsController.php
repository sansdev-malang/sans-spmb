<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbType;
use App\Models\Registration;
use Illuminate\Http\Request;
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

        return view('admin.settings-spmb', compact('periods', 'waves', 'types'));
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

        return redirect()->back()->with('success', 'Periode akademik berhasil ditambahkan.');
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

        // Lock editing name if used in registration
        if (Registration::where('spmb_period_id', $id)->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat mengubah periode yang sudah memiliki data pendaftaran aktif.');
        }

        $period->update(['year' => $request->year]);
        return redirect()->back()->with('success', 'Periode akademik berhasil diperbarui.');
    }

    public function destroyPeriod($id)
    {
        $period = SpmbPeriod::findOrFail($id);

        if (Registration::where('spmb_period_id', $id)->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus periode ini karena sudah memiliki transaksi pendaftaran aktif.');
        }

        $period->delete();
        return redirect()->back()->with('success', 'Periode akademik berhasil dihapus.');
    }

    // Wave CRUD
    public function storeWave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_waves,name'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'gelombang_create');
        }

        SpmbWave::create(['name' => $request->name]);

        return redirect()->back()->with('success', 'Gelombang pendaftaran berhasil ditambahkan.');
    }

    public function updateWave(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_waves,name,' . $id
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'gelombang_edit_' . $id);
        }

        $wave = SpmbWave::findOrFail($id);

        if (Registration::where('spmb_wave_id', $id)->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat mengubah gelombang yang sudah memiliki data pendaftaran aktif.');
        }

        $wave->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Gelombang pendaftaran berhasil diperbarui.');
    }

    public function destroyWave($id)
    {
        $wave = SpmbWave::findOrFail($id);

        if (Registration::where('spmb_wave_id', $id)->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus gelombang ini karena sudah digunakan dalam transaksi.');
        }

        $wave->delete();
        return redirect()->back()->with('success', 'Gelombang pendaftaran berhasil dihapus.');
    }

    // Type CRUD
    public function storeType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_types,name'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_create');
        }

        SpmbType::create(['name' => $request->name]);

        return redirect()->back()->with('success', 'Jenis pendaftaran berhasil ditambahkan.');
    }

    public function updateType(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_types,name,' . $id
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_edit_' . $id);
        }

        $type = SpmbType::findOrFail($id);

        if (Registration::where('spmb_type_id', $id)->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat mengubah jenis pendaftaran yang sudah memiliki data pendaftaran aktif.');
        }

        $type->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Jenis pendaftaran berhasil diperbarui.');
    }

    public function destroyType($id)
    {
        $type = SpmbType::findOrFail($id);

        if (Registration::where('spmb_type_id', $id)->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus jenis pendaftaran ini karena sudah memiliki data transaksi.');
        }

        $type->delete();
        return redirect()->back()->with('success', 'Jenis pendaftaran berhasil dihapus.');
    }
}
