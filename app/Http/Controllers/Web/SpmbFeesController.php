<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpmbFeeCategory;
use App\Models\SpmbFee;
use App\Models\Payment;
use App\Models\SpmbUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpmbFeesController extends Controller
{
    public function index()
    {
        $categoriesQuery = SpmbFeeCategory::query();
        if (!auth()->user()->isSuperAdmin()) {
            $categoriesQuery->whereHas('units', function ($q) {
                $q->where('spmb_units.id', auth()->user()->spmb_unit_id);
            });
        }
        $categories = $categoriesQuery->with('units')->get()->map(function ($cat) {
            $cat->is_used = SpmbFee::where('spmb_fee_category_id', $cat->id)->exists();
            return $cat;
        });

        $feesQuery = SpmbFee::query();
        if (!auth()->user()->isSuperAdmin()) {
            $feesQuery->where('spmb_unit_id', auth()->user()->spmb_unit_id);
        }
        $fees = $feesQuery->with('unit')->get()->map(function ($fee) {
            // Check if any payment matches the amount of this fee in the database
            $fee->is_used = Payment::where('amount', $fee->amount)->exists();
            return $fee;
        });

        $units = SpmbUnit::where('is_active', true)->get();
        $gateways = \App\Models\PaymentGateway::get();

        return view('admin.settings-fees', compact('categories', 'fees', 'units', 'gateways'));
    }

    // Fee Category (Jenis Biaya) CRUD
    public function storeCategory(Request $request)
    {
        $rules = [
            'name' => 'required|string|unique:spmb_fee_categories,name'
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['spmb_units'] = 'required|array|min:1';
            $rules['spmb_units.*'] = 'exists:spmb_units,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_biaya_create');
        }

        $category = SpmbFeeCategory::create(['name' => $request->name]);

        $units = auth()->user()->isSuperAdmin() ? $request->spmb_units : [auth()->user()->spmb_unit_id];
        $category->units()->sync($units);

        return redirect()->back()->with('success', 'Jenis biaya berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|unique:spmb_fee_categories,name,' . $id
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['spmb_units'] = 'required|array|min:1';
            $rules['spmb_units.*'] = 'exists:spmb_units,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_biaya_edit_' . $id);
        }

        $category = SpmbFeeCategory::findOrFail($id);
        $category->update(['name' => $request->name]);

        if (auth()->user()->isSuperAdmin()) {
            $category->units()->sync($request->spmb_units);
        }

        return redirect()->back()->with('success', 'Jenis biaya berhasil diperbarui.');
    }

    public function destroyCategory($id)
    {
        $category = SpmbFeeCategory::findOrFail($id);

        if (SpmbFee::where('spmb_fee_category_id', $category->id)->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus jenis biaya ini karena memiliki data nominal biaya aktif.');
        }

        $category->delete();
        return redirect()->back()->with('success', 'Jenis biaya berhasil dihapus.');
    }

    // Admin Fee (Biaya Admin) CRUD
    public function storeFee(Request $request)
    {
        $units = auth()->user()->isSuperAdmin() ? $request->spmb_units : [auth()->user()->spmb_unit_id];

        $gatewayCodes = \App\Models\PaymentGateway::pluck('code')->toArray();
        $rules = [
            'name' => 'required|string',
            'amount' => 'required|numeric|min:1000',
            'payment_gateway' => 'required|in:' . implode(',', $gatewayCodes),
            'spmb_fee_category_id' => 'required|exists:spmb_fee_categories,id',
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['spmb_units'] = 'required|array|min:1';
            $rules['spmb_units.*'] = 'exists:spmb_units,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'amount.min' => 'Nominal biaya pendaftaran minimal adalah Rp 1.000.'
        ]);

        $validator->after(function ($validator) use ($request, $units) {
            if ($request->name && $request->spmb_fee_category_id) {
                foreach ($units as $unitId) {
                    $exists = SpmbFee::where('name', $request->name)
                        ->where('spmb_fee_category_id', $request->spmb_fee_category_id)
                        ->where('spmb_unit_id', $unitId)
                        ->exists();
                    if ($exists) {
                        $unit = SpmbUnit::find($unitId);
                        $unitCode = $unit ? $unit->code : 'unit';
                        $validator->errors()->add('name', 'Nama biaya "' . $request->name . '" sudah digunakan pada unit ' . $unitCode . '.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'biaya_admin_create');
        }

        foreach ($units as $unitId) {
            SpmbFee::create([
                'name' => $request->name,
                'amount' => $request->amount,
                'payment_gateway' => $request->payment_gateway,
                'spmb_fee_category_id' => $request->spmb_fee_category_id,
                'spmb_unit_id' => $unitId,
            ]);
        }

        return redirect()->back()->with('success', 'Biaya pendaftaran berhasil ditambahkan.');
    }

    public function updateFee(Request $request, $id)
    {
        $unitId = auth()->user()->isSuperAdmin() ? ($request->spmb_units[0] ?? auth()->user()->spmb_unit_id) : auth()->user()->spmb_unit_id;

        $gatewayCodes = \App\Models\PaymentGateway::pluck('code')->toArray();
        $rules = [
            'name' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('spmb_fees')->ignore($id)->where(function ($query) use ($request, $unitId) {
                    return $query->where('spmb_fee_category_id', $request->spmb_fee_category_id)
                                 ->where('spmb_unit_id', $unitId);
                })
            ],
            'amount' => 'required|numeric|min:1000',
            'payment_gateway' => 'required|in:' . implode(',', $gatewayCodes),
            'spmb_fee_category_id' => 'required|exists:spmb_fee_categories,id',
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['spmb_units'] = 'required|array|min:1';
            $rules['spmb_units.*'] = 'exists:spmb_units,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'amount.min' => 'Nominal biaya pendaftaran minimal adalah Rp 1.000.',
            'name.unique' => 'Nama biaya sudah digunakan pada unit dan kategori ini.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'biaya_admin_edit_' . $id);
        }

        $fee = SpmbFee::findOrFail($id);

        if (Payment::whereIn('amount', [$fee->amount, $fee->amount + 1500, $fee->amount + 4500, round($fee->amount * 1.007)])->exists()) {
            if ($fee->amount != $request->amount) {
                return redirect()->back()->with('error', 'Tidak dapat mengubah nominal biaya yang sudah digunakan dalam transaksi.');
            }
        }

        $fee->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'payment_gateway' => $request->payment_gateway,
            'spmb_fee_category_id' => $request->spmb_fee_category_id,
            'spmb_unit_id' => $unitId,
        ]);

        return redirect()->back()->with('success', 'Biaya pendaftaran berhasil diperbarui.');
    }

    public function destroyFee($id)
    {
        $fee = SpmbFee::findOrFail($id);

        if (Payment::whereIn('amount', [$fee->amount, $fee->amount + 1500, $fee->amount + 4500, round($fee->amount * 1.007)])->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus biaya ini karena sudah terpakai pada transaksi pembayaran.');
        }

        $fee->delete();
        return redirect()->back()->with('success', 'Biaya pendaftaran berhasil dihapus.');
    }
}
