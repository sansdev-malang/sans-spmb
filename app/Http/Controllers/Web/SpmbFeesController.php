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
            $cat->is_used = SpmbFee::where('spmb_fee_category_id', $cat->id)
                ->get()
                ->contains(function ($fee) {
                    return self::isFeeUsed($fee);
                });
            return $cat;
        });

        $feesQuery = SpmbFee::query();
        if (!auth()->user()->isSuperAdmin()) {
            $feesQuery->where('spmb_unit_id', auth()->user()->spmb_unit_id);
        }
        $fees = $feesQuery->with(['unit', 'category'])->get()->map(function ($fee) {
            $fee->is_used = self::isFeeUsed($fee);
            return $fee;
        });

        $units = SpmbUnit::all();
        $periods = \App\Models\SpmbPeriod::orderBy('year', 'desc')->get();
        $gateways = \App\Models\PaymentGateway::get();
        $grades = \App\Models\SpmbGrade::orderBy('spmb_unit_id', 'asc')->orderBy('id', 'asc')->get();
        $classPrograms = \App\Models\SpmbClassProgram::with('units')->get();
        $types = \App\Models\SpmbType::with('units')->get();

        $activeTab = request()->get('tab', 'jenis_biaya');
        $selectedUnitId = request()->get('unit_id', '');
        $selectedPeriodId = request()->get('period_id', '');

        return view('admin.settings-fees', compact('categories', 'fees', 'units', 'periods', 'gateways', 'activeTab', 'grades', 'classPrograms', 'types', 'selectedUnitId', 'selectedPeriodId'));
    }

    // Fee Category (Jenis Biaya) CRUD
    public function storeCategory(Request $request)
    {
        $rules = [
            'name' => 'required|string|unique:spmb_fee_categories,name',
            'category_type' => 'required|in:registration_fee,tuition_fee,extra_service',
            'applicable_periods' => 'required|array|min:1',
            'applicable_periods.*' => 'exists:spmb_periods,id',
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['spmb_units'] = 'required|array|min:1';
            $rules['spmb_units.*'] = 'exists:spmb_units,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Nama jenis biaya wajib diisi.',
            'name.unique' => 'Nama jenis biaya sudah digunakan.',
            'applicable_periods.required' => 'Wajib memilih minimal satu Tahun Ajaran.',
            'applicable_periods.min' => 'Wajib memilih minimal satu Tahun Ajaran.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_biaya_create');
        }

        $applicablePeriods = !empty($request->applicable_periods) ? array_values(array_map('intval', (array)$request->applicable_periods)) : null;

        $category = SpmbFeeCategory::create([
            'name' => $request->name,
            'category_type' => $request->category_type ?? SpmbFeeCategory::TYPE_TUITION,
            'applicable_periods' => $applicablePeriods,
        ]);

        $units = auth()->user()->isSuperAdmin() ? $request->spmb_units : [auth()->user()->spmb_unit_id];
        $category->units()->sync($units);

        return redirect()->route('admin.spmb-settings.fees', ['tab' => 'jenis_biaya'])->with('success', 'Jenis biaya berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|unique:spmb_fee_categories,name,' . $id,
            'category_type' => 'required|in:registration_fee,tuition_fee,extra_service',
            'applicable_periods' => 'required|array|min:1',
            'applicable_periods.*' => 'exists:spmb_periods,id',
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['spmb_units'] = 'required|array|min:1';
            $rules['spmb_units.*'] = 'exists:spmb_units,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Nama jenis biaya wajib diisi.',
            'name.unique' => 'Nama jenis biaya sudah digunakan.',
            'applicable_periods.required' => 'Wajib memilih minimal satu Tahun Ajaran.',
            'applicable_periods.min' => 'Wajib memilih minimal satu Tahun Ajaran.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'jenis_biaya_edit_' . $id);
        }

        $applicablePeriods = !empty($request->applicable_periods) ? array_values(array_map('intval', (array)$request->applicable_periods)) : null;

        $category = SpmbFeeCategory::findOrFail($id);
        $category->update([
            'name' => $request->name,
            'category_type' => $request->category_type ?? $category->category_type,
            'applicable_periods' => $applicablePeriods,
        ]);

        if (auth()->user()->isSuperAdmin()) {
            $category->units()->sync($request->spmb_units);
        }

        self::syncUnpaidRegistrationsFeeSnapshot();

        return redirect()->route('admin.spmb-settings.fees', ['tab' => 'jenis_biaya'])->with('success', 'Jenis biaya berhasil diperbarui.');
    }

    public function trashCategory($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Fitur Tong Sampah hanya dapat diakses oleh Super Admin.');
        }

        $category = SpmbFeeCategory::findOrFail($id);

        $category->update([
            'is_testing' => true,
        ]);

        self::syncUnpaidRegistrationsFeeSnapshot();

        return redirect()->route('admin.spmb-settings.fees', ['tab' => 'jenis_biaya'])
            ->with('success', 'Jenis biaya "' . $category->name . '" berhasil dipindahkan ke Tong Sampah.');
    }

    public function restoreCategory($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Fitur Tong Sampah hanya dapat diakses oleh Super Admin.');
        }

        $category = SpmbFeeCategory::findOrFail($id);

        $category->update([
            'is_testing' => false,
        ]);

        self::syncUnpaidRegistrationsFeeSnapshot();

        return redirect()->route('admin.spmb-settings.fees', ['tab' => 'jenis_biaya'])
            ->with('success', 'Jenis biaya "' . $category->name . '" berhasil dipulihkan ke tab utama.');
    }

    public function destroyCategory($id)
    {
        $category = SpmbFeeCategory::findOrFail($id);

        if (SpmbFee::where('spmb_fee_category_id', $category->id)->exists()) {
            return redirect()->route('admin.spmb-settings.fees', ['tab' => 'jenis_biaya'])->with('error', 'Tidak dapat menghapus jenis biaya ini karena memiliki data nominal biaya aktif.');
        }

        $category->delete();
        self::syncUnpaidRegistrationsFeeSnapshot();
        return redirect()->route('admin.spmb-settings.fees', ['tab' => 'jenis_biaya'])->with('success', 'Jenis biaya berhasil dihapus.');
    }

    // Admin Fee (Biaya Admin) CRUD
    public function storeFee(Request $request)
    {
        if ($request->has('amount')) {
            $cleanedAmount = preg_replace('/[^0-9]/', '', (string) $request->amount);
            $request->merge(['amount' => $cleanedAmount]);
        }

        $units = auth()->user()->isSuperAdmin() ? $request->spmb_units : [auth()->user()->spmb_unit_id];

        $gatewayCodes = \App\Models\PaymentGateway::pluck('code')->toArray();
        $rules = [
            'name' => 'required|string',
            'amount' => 'required|numeric|min:1000|max:9999999999',
            'payment_gateway' => 'required|array|min:1',
            'payment_gateway.*' => 'in:' . implode(',', $gatewayCodes),
            'spmb_fee_category_id' => 'required|exists:spmb_fee_categories,id',
            'applicable_periods' => 'required|array|min:1',
            'applicable_periods.*' => 'exists:spmb_periods,id',
            'applicable_grades' => 'nullable|array',
            'applicable_grades.*' => 'exists:spmb_grades,id',
            'applicable_class_programs' => 'nullable|array',
            'applicable_class_programs.*' => 'exists:spmb_class_programs,id',
            'applicable_types' => 'nullable|array',
            'applicable_types.*' => 'exists:spmb_types,id',
            'applicable_gender' => 'nullable|string|in:all,male,female,laki-laki,perempuan',
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['spmb_units'] = 'required|array|min:1';
            $rules['spmb_units.*'] = 'exists:spmb_units,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'amount.required' => 'Nominal biaya wajib diisi.',
            'amount.numeric' => 'Nominal biaya harus berupa angka.',
            'amount.min' => 'Nominal biaya pendaftaran minimal adalah Rp 1.000.',
            'amount.max' => 'Nominal biaya pendaftaran maksimal adalah Rp 9.999.999.999.',
            'applicable_periods.required' => 'Wajib memilih minimal satu Tahun Ajaran.',
            'applicable_periods.min' => 'Wajib memilih minimal satu Tahun Ajaran.',
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

        $applicablePeriods = !empty($request->applicable_periods) ? array_values(array_map('intval', (array)$request->applicable_periods)) : null;
        $applicableGrades = !empty($request->applicable_grades) ? array_values(array_map('intval', (array)$request->applicable_grades)) : null;
        $applicableClassPrograms = !empty($request->applicable_class_programs) ? array_values(array_map('intval', (array)$request->applicable_class_programs)) : null;
        $applicableTypes = !empty($request->applicable_types) ? array_values(array_map('intval', (array)$request->applicable_types)) : null;
        $applicableGender = (!empty($request->applicable_gender) && $request->applicable_gender !== 'all') ? $request->applicable_gender : null;
        $isTesting = $request->boolean('is_testing', false);

        foreach ($units as $unitId) {
            SpmbFee::create([
                'name' => $request->name,
                'amount' => $request->amount,
                'payment_gateway' => $request->payment_gateway,
                'spmb_fee_category_id' => $request->spmb_fee_category_id,
                'spmb_unit_id' => $unitId,
                'applicable_periods' => $applicablePeriods,
                'applicable_grades' => $applicableGrades,
                'applicable_class_programs' => $applicableClassPrograms,
                'applicable_types' => $applicableTypes,
                'applicable_gender' => $applicableGender,
                'is_testing' => $isTesting,
                'is_active' => true,
            ]);
        }

        self::syncUnpaidRegistrationsFeeSnapshot($units);

        $targetTab = $isTesting ? 'test_cat_' . $request->spmb_fee_category_id : 'cat_' . $request->spmb_fee_category_id;
        return redirect()->route('admin.spmb-settings.fees', ['tab' => $targetTab])->with('success', 'Biaya pendaftaran ' . ($isTesting ? '(Mode Testing) ' : '') . 'berhasil ditambahkan.');
    }

    public function updateFee(Request $request, $id)
    {
        if ($request->has('amount')) {
            $cleanedAmount = preg_replace('/[^0-9]/', '', (string) $request->amount);
            $request->merge(['amount' => $cleanedAmount]);
        }

        $fee = SpmbFee::findOrFail($id);
        $unitId = auth()->user()->isSuperAdmin() ? ($request->spmb_units[0] ?? $fee->spmb_unit_id) : (auth()->user()->spmb_unit_id ?? $fee->spmb_unit_id);

        $gatewayCodes = \App\Models\PaymentGateway::pluck('code')->toArray();
        $rules = [
            'name' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('spmb_fees')->ignore($id)->where(function ($query) use ($request, $unitId, $fee) {
                    return $query->where('spmb_fee_category_id', $request->spmb_fee_category_id ?? $fee->spmb_fee_category_id)
                                 ->where('spmb_unit_id', $unitId);
                })
            ],
            'amount' => 'required|numeric|min:1000|max:9999999999',
            'payment_gateway' => 'required|array|min:1',
            'payment_gateway.*' => 'in:' . implode(',', $gatewayCodes),
            'spmb_fee_category_id' => 'required|exists:spmb_fee_categories,id',
            'applicable_periods' => 'required|array|min:1',
            'applicable_periods.*' => 'exists:spmb_periods,id',
            'applicable_grades' => 'nullable|array',
            'applicable_grades.*' => 'exists:spmb_grades,id',
            'applicable_class_programs' => 'nullable|array',
            'applicable_class_programs.*' => 'exists:spmb_class_programs,id',
            'applicable_types' => 'nullable|array',
            'applicable_types.*' => 'exists:spmb_types,id',
            'applicable_gender' => 'nullable|string|in:all,male,female,laki-laki,perempuan',
        ];

        if (auth()->user()->isSuperAdmin() && $request->has('spmb_units')) {
            $rules['spmb_units'] = 'nullable|array';
            $rules['spmb_units.*'] = 'exists:spmb_units,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'amount.required' => 'Nominal biaya wajib diisi.',
            'amount.numeric' => 'Nominal biaya harus berupa angka.',
            'amount.min' => 'Nominal biaya pendaftaran minimal adalah Rp 1.000.',
            'amount.max' => 'Nominal biaya pendaftaran maksimal adalah Rp 9.999.999.999.',
            'name.unique' => 'Nama biaya sudah digunakan pada unit dan kategori ini.',
            'applicable_periods.required' => 'Wajib memilih minimal satu Tahun Ajaran.',
            'applicable_periods.min' => 'Wajib memilih minimal satu Tahun Ajaran.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'biaya_admin_edit_' . $id);
        }

        if (self::isFeeUsed($fee)) {
            if ((int)$fee->amount != (int)$request->amount) {
                $targetTab = $fee->is_testing ? 'test_cat_' . $fee->spmb_fee_category_id : 'cat_' . $fee->spmb_fee_category_id;
                return redirect()->route('admin.spmb-settings.fees', ['tab' => $targetTab])->with('error', 'Tidak dapat mengubah nominal biaya yang sudah digunakan dalam transaksi.');
            }
        }

        $applicablePeriods = !empty($request->applicable_periods) ? array_values(array_map('intval', (array)$request->applicable_periods)) : null;
        $applicableGrades = !empty($request->applicable_grades) ? array_values(array_map('intval', (array)$request->applicable_grades)) : null;
        $applicableClassPrograms = !empty($request->applicable_class_programs) ? array_values(array_map('intval', (array)$request->applicable_class_programs)) : null;
        $applicableTypes = !empty($request->applicable_types) ? array_values(array_map('intval', (array)$request->applicable_types)) : null;
        $applicableGender = (!empty($request->applicable_gender) && $request->applicable_gender !== 'all') ? $request->applicable_gender : null;
        $isTesting = $request->boolean('is_testing', false);

        $fee->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'payment_gateway' => $request->payment_gateway,
            'spmb_fee_category_id' => $request->spmb_fee_category_id,
            'spmb_unit_id' => $unitId,
            'applicable_periods' => $applicablePeriods,
            'applicable_grades' => $applicableGrades,
            'applicable_class_programs' => $applicableClassPrograms,
            'applicable_types' => $applicableTypes,
            'applicable_gender' => $applicableGender,
            'is_testing' => $isTesting,
        ]);

        self::syncUnpaidRegistrationsFeeSnapshot([$unitId]);

        $targetTab = $isTesting ? 'test_cat_' . $request->spmb_fee_category_id : 'cat_' . $request->spmb_fee_category_id;
        return redirect()->route('admin.spmb-settings.fees', ['tab' => $targetTab])->with('success', 'Biaya pendaftaran ' . ($isTesting ? '(Mode Testing) ' : '') . 'berhasil diperbarui.');
    }

    public function trashFee($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Fitur Tong Sampah hanya dapat diakses oleh Super Admin.');
        }

        $fee = SpmbFee::findOrFail($id);

        $fee->update([
            'is_testing' => true,
        ]);

        self::syncUnpaidRegistrationsFeeSnapshot([$fee->spmb_unit_id]);

        return redirect()->route('admin.spmb-settings.fees', ['tab' => 'cat_' . $fee->spmb_fee_category_id])
            ->with('success', 'Komponen biaya "' . $fee->name . '" berhasil dipindahkan ke Tong Sampah.');
    }

    public function restoreFee($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Fitur Tong Sampah hanya dapat diakses oleh Super Admin.');
        }

        $fee = SpmbFee::findOrFail($id);

        $fee->update([
            'is_testing' => false,
        ]);

        self::syncUnpaidRegistrationsFeeSnapshot([$fee->spmb_unit_id]);

        return redirect()->route('admin.spmb-settings.fees', ['tab' => 'cat_' . $fee->spmb_fee_category_id])
            ->with('success', 'Komponen biaya "' . $fee->name . '" berhasil dipulihkan ke tab utama.');
    }

    public function destroyFee($id)
    {
        $fee = SpmbFee::findOrFail($id);

        $catId = $fee->spmb_fee_category_id;
        $unitId = $fee->spmb_unit_id;
        $isTesting = (bool) $fee->is_testing;
        $targetTab = $isTesting ? 'test_cat_' . $catId : 'cat_' . $catId;

        if (self::isFeeUsed($fee)) {
            return redirect()->route('admin.spmb-settings.fees', ['tab' => $targetTab])->with('error', 'Tidak dapat menghapus biaya ini karena sudah terpakai pada transaksi pembayaran.');
        }

        $fee->delete();
        self::syncUnpaidRegistrationsFeeSnapshot([$unitId]);

        return redirect()->route('admin.spmb-settings.fees', ['tab' => $targetTab])->with('success', 'Biaya pendaftaran berhasil dihapus.');
    }

    public static function syncUnpaidRegistrationsFeeSnapshot($unitIds = [])
    {
        $query = \App\Models\Registration::query();
        if (!empty($unitIds)) {
            $query->whereIn('spmb_unit_id', (array) $unitIds);
        }

        $registrations = $query->get()->filter(function ($r) {
            return $r->total_paid_final_fee <= 0;
        });

        foreach ($registrations as $r) {
            $freshFees = $r->getFinalFeeDetails(true);
            $r->update(['final_fee_snapshot' => $freshFees]);
        }
    }

    public static function isFeeUsed($fee)
    {
        // 0. Direct check via PaymentItem relation if available
        $hasPaymentItem = \App\Models\PaymentItem::where('spmb_fee_id', $fee->id)
            ->whereHas('payment', function ($q) {
                $q->whereIn('status', ['success', 'pending']);
            })
            ->exists();

        if ($hasPaymentItem) {
            return true;
        }

        // 1. If it's a registration fee category or registration fee
        $isRegFee = ($fee->category && preg_match('/(formulir|pendaftaran|registrasi|enrollment|registration)/i', $fee->category->name))
            || preg_match('/(formulir|pendaftaran|registrasi|enrollment|registration)/i', $fee->name);

        if ($isRegFee) {
            return Payment::where('payment_type', 'registration_fee')
                ->whereIn('status', ['success', 'pending'])
                ->whereHas('registration', function ($q) use ($fee) {
                    $q->where('spmb_unit_id', $fee->spmb_unit_id);
                })
                ->where(function ($q) use ($fee) {
                    $q->where('base_amount', $fee->amount)
                      ->orWhereIn('amount', [
                          $fee->amount,
                          $fee->amount + 1500,
                          $fee->amount + 4500,
                          round($fee->amount * 1.007)
                      ]);
                })
                ->exists();
        }

        // 2. For final_fee payment type (additional fees like Seragam, Uang Gedung)
        return Payment::where('payment_type', 'final_fee')
            ->whereIn('status', ['success', 'pending'])
            ->whereHas('registration', function ($q) use ($fee) {
                $q->where('spmb_unit_id', $fee->spmb_unit_id);
            })
            ->get()
            ->contains(function ($payment) use ($fee) {
                $items = $payment->payment_info['selected_items'] ?? [];
                return collect($items)->contains('name', $fee->name);
            });
    }
}
