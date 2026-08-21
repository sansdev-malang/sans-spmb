<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpmbFeeCategory;
use App\Models\SpmbFee;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpmbFeesController extends Controller
{
    public function index()
    {
        $categories = SpmbFeeCategory::all()->map(function ($cat) {
            // Cannot delete Biaya Pendaftaran since it's the core system type
            $cat->is_used = ($cat->name === 'Biaya Pendaftaran');
            return $cat;
        });

        $fees = SpmbFee::all()->map(function ($fee) {
            // Check if any payment matches the amount of this fee in the database
            $fee->is_used = Payment::where('amount', $fee->amount)->exists();
            return $fee;
        });

        return view('admin.settings-fees', compact('categories', 'fees'));
    }

    // Fee Category (Jenis Biaya) CRUD
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_fee_categories,name'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_biaya_create');
        }

        SpmbFeeCategory::create(['name' => $request->name]);

        return redirect()->back()->with('success', 'Jenis biaya berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_fee_categories,name,' . $id
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_biaya_edit_' . $id);
        }

        $category = SpmbFeeCategory::findOrFail($id);

        if ($category->name === 'Biaya Pendaftaran') {
            return redirect()->back()->with('error', 'Tidak dapat mengubah kategori Biaya Pendaftaran utama.');
        }

        $category->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Jenis biaya berhasil diperbarui.');
    }

    public function destroyCategory($id)
    {
        $category = SpmbFeeCategory::findOrFail($id);

        if ($category->name === 'Biaya Pendaftaran') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus kategori Biaya Pendaftaran karena terikat sistem pendaftaran.');
        }

        $category->delete();
        return redirect()->back()->with('success', 'Jenis biaya berhasil dihapus.');
    }

    // Admin Fee (Biaya Admin) CRUD
    public function storeFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_fees,name',
            'amount' => 'required|numeric|min:1000',
            'payment_gateway' => 'required|in:winpay,bni',
        ], [
            'amount.min' => 'Nominal biaya pendaftaran minimal adalah Rp 1.000.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'biaya_admin_create');
        }

        SpmbFee::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'payment_gateway' => $request->payment_gateway,
        ]);

        return redirect()->back()->with('success', 'Biaya pendaftaran berhasil ditambahkan.');
    }

    public function updateFee(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:spmb_fees,name,' . $id,
            'amount' => 'required|numeric|min:1000',
            'payment_gateway' => 'required|in:winpay,bni',
        ], [
            'amount.min' => 'Nominal biaya pendaftaran minimal adalah Rp 1.000.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'biaya_admin_edit_' . $id);
        }

        $fee = SpmbFee::findOrFail($id);

        if (Payment::where('amount', $fee->amount)->exists()) {
            // we should still allow updating payment_gateway and name, but amount check can be stricter or not
            // actually, let's keep the existing logic that prevents any change if it's used.
            // But wait, the user might want to change gateway even if it's used.
            // Let's modify the amount constraint: only fail if the amount changed.
            if ($fee->amount != $request->amount) {
                return redirect()->back()->with('error', 'Tidak dapat mengubah nominal biaya yang sudah digunakan dalam transaksi.');
            }
        }

        $fee->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'payment_gateway' => $request->payment_gateway,
        ]);

        return redirect()->back()->with('success', 'Biaya pendaftaran berhasil diperbarui.');
    }

    public function destroyFee($id)
    {
        $fee = SpmbFee::findOrFail($id);

        if (Payment::where('amount', $fee->amount)->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus biaya ini karena sudah terpakai pada transaksi pembayaran.');
        }

        $fee->delete();
        return redirect()->back()->with('success', 'Biaya pendaftaran berhasil dihapus.');
    }
}
