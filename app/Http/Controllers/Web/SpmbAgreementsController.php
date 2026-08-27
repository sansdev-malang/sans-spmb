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
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Load only the admin's unit if they are not super admin
        $unitsQuery = SpmbUnit::with('agreementTemplate');
        if (!$isSuperAdmin) {
            $unitsQuery->where('id', auth()->user()->spmb_unit_id);
        }
        $units = $unitsQuery->get();

        return view('admin.settings-agreements', compact('units'));
    }
    public function update(Request $request, $id)
    {
        if (!auth()->user()->isSuperAdmin() && $id != auth()->user()->spmb_unit_id) {
            abort(403, 'Unauthorized action.');
        }

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

        $activeTab = $request->input('active_tab', 'unit_' . $id);
        return redirect()->route('admin.spmb-settings.agreements', ['tab' => $activeTab])
            ->with('success', 'Template Surat Pernyataan berhasil diperbarui.');
    }
}
