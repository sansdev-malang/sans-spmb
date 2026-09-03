<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpmbFeeCategory;
use App\Models\SpmbFee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SpmbFeeCategoryAndFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Fee Categories
        $feeCategories = [
            ['name' => 'Formulir Pendaftaran'],
            ['name' => 'Biaya Administrasi'],
            ['name' => 'Biaya Tambahan'],
        ];
        foreach ($feeCategories as $category) {
            SpmbFeeCategory::updateOrCreate(['name' => $category['name']], $category);
        }

        // Map Category IDs
        $catFormulir = SpmbFeeCategory::where('name', 'Formulir Pendaftaran')->first()?->id ?? 1;
        $catAdmin = SpmbFeeCategory::where('name', 'Biaya Administrasi')->first()?->id ?? 2;
        $catTambahan = SpmbFeeCategory::where('name', 'Biaya Tambahan')->first()?->id ?? 3;

        // 2. Fees
        $fees = [
            [
                'spmb_fee_category_id' => $catFormulir,
                'spmb_unit_id' => 1,
                'name' => 'Formulir Pendaftaran PAUD',
                'amount' => 350000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catFormulir,
                'spmb_unit_id' => 2,
                'name' => 'Formulir Pendaftaran SD',
                'amount' => 350000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => 1,
                'name' => "Uang Gedung (Musa'adah)",
                'amount' => 3500000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => 1,
                'name' => 'Biaya Seragam',
                'amount' => 1200000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => 2,
                'name' => "Uang Gedung (Musa'adah)",
                'amount' => 13000000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => 2,
                'name' => 'Biaya Seragam',
                'amount' => 1800000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catFormulir,
                'spmb_unit_id' => 3,
                'name' => 'Formulir Pendaftaran SMP',
                'amount' => 350000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => 2,
                'name' => 'Biaya Buku Cambridge',
                'amount' => 1400000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => 3,
                'name' => "Uang Gedung (Musa'adah)",
                'amount' => 15000000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => 3,
                'name' => 'Biaya Seragam',
                'amount' => 1500000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'spmb_fee_category_id' => $catTambahan,
                'spmb_unit_id' => 1,
                'name' => 'TPA',
                'amount' => 1400000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'spmb_fee_category_id' => $catTambahan,
                'spmb_unit_id' => 1,
                'name' => 'TPQ',
                'amount' => 1500000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
        ];
        foreach ($fees as $fee) {
            $fee['payment_gateway'] = is_array($fee['payment_gateway']) ? $fee['payment_gateway'] : [$fee['payment_gateway']];
            SpmbFee::updateOrCreate(
                ['spmb_unit_id' => $fee['spmb_unit_id'], 'name' => $fee['name']],
                $fee
            );
        }

        // 3. Pivot spmb_fee_category_unit
        if (Schema::hasTable('spmb_fee_category_unit')) {
            DB::table('spmb_fee_category_unit')->truncate();
            $pivot = [
                ['spmb_fee_category_id' => $catTambahan, 'spmb_unit_id' => 1],
                ['spmb_fee_category_id' => $catTambahan, 'spmb_unit_id' => 2],
                ['spmb_fee_category_id' => $catTambahan, 'spmb_unit_id' => 3],
                ['spmb_fee_category_id' => $catAdmin, 'spmb_unit_id' => 1],
                ['spmb_fee_category_id' => $catAdmin, 'spmb_unit_id' => 2],
                ['spmb_fee_category_id' => $catAdmin, 'spmb_unit_id' => 3],
                ['spmb_fee_category_id' => $catFormulir, 'spmb_unit_id' => 1],
                ['spmb_fee_category_id' => $catFormulir, 'spmb_unit_id' => 2],
                ['spmb_fee_category_id' => $catFormulir, 'spmb_unit_id' => 3],
            ];
            DB::table('spmb_fee_category_unit')->insert($pivot);
        }
    }
}
