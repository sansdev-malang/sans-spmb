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
        $steps = SpmbFormStep::with('fields')->orderBy('order')->get();
        return view('admin.settings-form', compact('steps'));
    }

    public function storeStep(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'required|integer',
        ]);

        SpmbFormStep::create([
            'title' => $request->title,
            'order' => $request->order,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Langkah formulir berhasil ditambahkan.');
    }

    public function updateStep(Request $request, $id)
    {
        $step = SpmbFormStep::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'required|integer',
        ]);

        $step->update([
            'title' => $request->title,
            'order' => $request->order,
        ]);

        return redirect()->back()->with('success', 'Langkah formulir berhasil diperbarui.');
    }

    public function destroyStep($id)
    {
        $step = SpmbFormStep::with('fields')->findOrFail($id);

        foreach ($step->fields as $field) {
            if (\App\Models\Registration::whereNotNull("additional_info->{$field->field_name}")->exists()) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus langkah formulir ini karena kolom di dalamnya ("' . $field->label . '") sudah diisi oleh pendaftar.');
            }
        }

        $step->delete();
        return redirect()->back()->with('success', 'Langkah formulir berhasil dihapus.');
    }

    public function storeField(Request $request)
    {
        $request->validate([
            'form_step_id' => 'required|exists:spmb_form_steps,id',
            'label' => 'required|string|max:255',
            'field_name' => 'required|string|max:255',
            'type' => 'required|in:text,number,email,date,select,textarea,file',
            'options' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'order' => 'required|integer',
        ]);

        SpmbFormField::create([
            'form_step_id' => $request->form_step_id,
            'label' => $request->label,
            'field_name' => $request->field_name,
            'type' => $request->type,
            'options' => $request->options,
            'is_required' => $request->has('is_required'),
            'order' => $request->order,
        ]);

        return redirect()->back()->with('success', 'Kolom input formulir berhasil ditambahkan.');
    }

    public function updateField(Request $request, $id)
    {
        $field = SpmbFormField::findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:255',
            'field_name' => 'required|string|max:255',
            'type' => 'required|in:text,number,email,date,select,textarea,file',
            'options' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'order' => 'required|integer',
        ]);

        $systemFields = ['candidate_name', 'spmb_period_id', 'spmb_wave_id', 'spmb_type_id', 'spmb_class_program_id'];
        if (in_array($field->field_name, $systemFields) && $request->field_name !== $field->field_name) {
            return redirect()->back()->with('error', 'Key database kolom sistem utama tidak boleh diubah.');
        }

        $field->update([
            'label' => $request->label,
            'field_name' => $request->field_name,
            'type' => $request->type,
            'options' => $request->options,
            'is_required' => $request->has('is_required'),
            'order' => $request->order,
        ]);

        return redirect()->back()->with('success', 'Kolom input formulir berhasil diperbarui.');
    }

    public function destroyField($id)
    {
        $field = SpmbFormField::findOrFail($id);

        $systemFields = ['candidate_name', 'spmb_period_id', 'spmb_wave_id', 'spmb_type_id', 'spmb_class_program_id'];
        if (in_array($field->field_name, $systemFields)) {
            return redirect()->back()->with('error', 'Kolom sistem utama tidak boleh dihapus.');
        }

        if (\App\Models\Registration::whereNotNull("additional_info->{$field->field_name}")->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus kolom input ini karena sudah diisi oleh pendaftar.');
        }

        $field->delete();
        return redirect()->back()->with('success', 'Kolom input formulir berhasil dihapus.');
    }
}
