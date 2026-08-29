<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpmbFormStep;
use App\Models\SpmbFormField;

class SpmbFormSettingsController extends Controller
{
    public function index()
    {
        $units = \App\Models\SpmbUnit::where('is_active', true)->get();
        $selectedUnitId = request()->get('unit_id', ''); // '' means 'All Units' / Global

        $steps = SpmbFormStep::with(['fields' => function($q) use ($selectedUnitId) {
                if ($selectedUnitId !== '') {
                    $q->where(function($sub) use ($selectedUnitId) {
                        $sub->whereNull('spmb_unit_id')->orWhere('spmb_unit_id', $selectedUnitId);
                    });
                }
                $q->orderBy('order');
            }])
            ->when($selectedUnitId !== '', function($q) use ($selectedUnitId) {
                $q->where(function($sub) use ($selectedUnitId) {
                    $sub->whereNull('spmb_unit_id')->orWhere('spmb_unit_id', $selectedUnitId);
                });
            })
            ->orderBy('order')
            ->get();

        $activeTab = request()->get('tab', 'crud_steps');
        return view('admin.settings-form', compact('steps', 'activeTab', 'units', 'selectedUnitId'));
    }

    public function storeStep(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'order' => 'required|integer',
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.form', ['tab' => 'crud_steps', 'unit_id' => $request->spmb_unit_id])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'step_create');
        }

        SpmbFormStep::create([
            'title' => $request->title,
            'order' => $request->order,
            'spmb_unit_id' => $request->spmb_unit_id ?: null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.spmb-settings.form', ['tab' => 'crud_steps', 'unit_id' => $request->spmb_unit_id])->with('success', 'Langkah formulir berhasil ditambahkan.');
    }

    public function updateStep(Request $request, $id)
    {
        $step = SpmbFormStep::findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'order' => 'required|integer',
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.form', ['tab' => 'crud_steps', 'unit_id' => $request->spmb_unit_id])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'step_edit_' . $id);
        }

        $step->update([
            'title' => $request->title,
            'order' => $request->order,
            'spmb_unit_id' => $request->spmb_unit_id ?: null,
        ]);

        return redirect()->route('admin.spmb-settings.form', ['tab' => 'crud_steps', 'unit_id' => $request->spmb_unit_id])->with('success', 'Langkah formulir berhasil diperbarui.');
    }

    public function destroyStep($id)
    {
        $step = SpmbFormStep::with('fields')->findOrFail($id);

        foreach ($step->fields as $field) {
            if (\App\Models\Registration::whereNotNull("additional_info->{$field->field_name}")->exists()) {
                return redirect()->route('admin.spmb-settings.form', ['tab' => 'crud_steps'])->with('error', 'Tidak dapat menghapus langkah formulir ini karena kolom di dalamnya ("' . $field->label . '") sudah diisi oleh pendaftar.');
            }
        }

        $step->delete();
        return redirect()->route('admin.spmb-settings.form', ['tab' => 'crud_steps'])->with('success', 'Langkah formulir berhasil dihapus.');
    }

    public function storeField(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'form_step_id' => 'required|exists:spmb_form_steps,id',
            'label' => 'required|string|max:255',
            'field_name' => 'required|string|max:255',
            'type' => 'required|in:text,number,email,date,select,textarea,file',
            'options' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'order' => 'required|integer',
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.form', ['tab' => 'step_' . $request->form_step_id, 'unit_id' => $request->spmb_unit_id])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'field_create_' . $request->form_step_id);
        }

        SpmbFormField::create([
            'form_step_id' => $request->form_step_id,
            'label' => $request->label,
            'field_name' => $request->field_name,
            'type' => $request->type,
            'options' => $request->options,
            'is_required' => $request->has('is_required'),
            'order' => $request->order,
            'spmb_unit_id' => $request->spmb_unit_id ?: null,
        ]);

        return redirect()->route('admin.spmb-settings.form', ['tab' => 'step_' . $request->form_step_id, 'unit_id' => $request->spmb_unit_id])->with('success', 'Kolom input formulir berhasil ditambahkan.');
    }

    public function updateField(Request $request, $id)
    {
        $field = SpmbFormField::findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'field_name' => 'required|string|max:255',
            'type' => 'required|in:text,number,email,date,select,textarea,file',
            'options' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'order' => 'required|integer',
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.spmb-settings.form', ['tab' => 'step_' . $field->form_step_id, 'unit_id' => $request->spmb_unit_id])
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'field_edit_' . $id);
        }

        $systemFields = ['candidate_name', 'spmb_period_id', 'spmb_wave_id', 'spmb_type_id', 'spmb_class_program_id'];
        if (in_array($field->field_name, $systemFields) && $request->field_name !== $field->field_name) {
            return redirect()->route('admin.spmb-settings.form', ['tab' => 'step_' . $field->form_step_id, 'unit_id' => $request->spmb_unit_id])->with('error', 'Key database kolom sistem utama tidak boleh diubah.');
        }

        $field->update([
            'label' => $request->label,
            'field_name' => $request->field_name,
            'type' => $request->type,
            'options' => $request->options,
            'is_required' => $request->has('is_required'),
            'order' => $request->order,
            'spmb_unit_id' => $request->spmb_unit_id ?: null,
        ]);

        return redirect()->route('admin.spmb-settings.form', ['tab' => 'step_' . $field->form_step_id, 'unit_id' => $request->spmb_unit_id])->with('success', 'Kolom input formulir berhasil diperbarui.');
    }

    public function destroyField($id)
    {
        $field = SpmbFormField::findOrFail($id);
        $unitId = request()->get('unit_id', '');

        $systemFields = ['candidate_name', 'spmb_period_id', 'spmb_wave_id', 'spmb_type_id', 'spmb_class_program_id'];
        if (in_array($field->field_name, $systemFields)) {
            return redirect()->route('admin.spmb-settings.form', ['tab' => 'step_' . $field->form_step_id, 'unit_id' => $unitId])->with('error', 'Kolom sistem utama tidak boleh dihapus.');
        }

        if (\App\Models\Registration::whereNotNull("additional_info->{$field->field_name}")->exists()) {
            return redirect()->route('admin.spmb-settings.form', ['tab' => 'step_' . $field->form_step_id, 'unit_id' => $unitId])->with('error', 'Tidak dapat menghapus kolom input ini karena sudah diisi oleh pendaftar.');
        }

        $field->delete();
        return redirect()->route('admin.spmb-settings.form', ['tab' => 'step_' . $field->form_step_id, 'unit_id' => $unitId])->with('success', 'Kolom input formulir berhasil dihapus.');
    }
}
