<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpmbUnit;
use App\Models\SpmbAgreementTemplate;
use Illuminate\Http\Request;

class SpmbAgreementsController extends Controller
{
    public function index()
    {
        // Load all units with their respective agreement template
        $units = SpmbUnit::with('agreementTemplate')->get();

        return view('admin.settings-agreements', compact('units'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'rules_consent_label' => 'required|string|max:255',
            'fees_consent_label' => 'required|string|max:255',
            'place' => 'required|string|max:255',
            'principal_name' => 'required|string|max:255',
            'principal_title' => 'required|string|max:255',
        ]);

        $template = SpmbAgreementTemplate::updateOrCreate(
            ['spmb_unit_id' => $id],
            [
                'title' => $request->title,
                'content' => $request->content,
                'rules_consent_label' => $request->rules_consent_label,
                'fees_consent_label' => $request->fees_consent_label,
                'place' => $request->place,
                'principal_name' => $request->principal_name,
                'principal_title' => $request->principal_title,
            ]
        );

        return redirect()->back()->with('success', 'Template Surat Pernyataan berhasil diperbarui.');
    }
}
