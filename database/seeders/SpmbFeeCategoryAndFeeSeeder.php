<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpmbFeeCategory;
use App\Models\SpmbFee;
use App\Models\SpmbUnit;
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

        // Map Unit IDs
        $unitPaud = SpmbUnit::where('code', 'PAUD')->first()?->id ?? 1;
        $unitSd = SpmbUnit::where('code', 'SD')->first()?->id ?? 2;
        $unitSmp = SpmbUnit::where('code', 'SMP')->first()?->id ?? 3;

        // 2. Fees
        $fees = [
            [
                'spmb_fee_category_id' => $catFormulir,
                'spmb_unit_id' => $unitPaud,
                'name' => 'Formulir Pendaftaran PAUD',
                'amount' => 350000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catFormulir,
                'spmb_unit_id' => $unitSd,
                'name' => 'Formulir Pendaftaran SD',
                'amount' => 350000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => $unitPaud,
                'name' => "Uang Gedung (Musa'adah)",
                'amount' => 3500000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => $unitPaud,
                'name' => 'Biaya Seragam',
                'amount' => 1200000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => $unitSd,
                'name' => "Uang Gedung (Musa'adah)",
                'amount' => 13000000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => $unitSd,
                'name' => 'Biaya Seragam',
                'amount' => 1800000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catFormulir,
                'spmb_unit_id' => $unitSmp,
                'name' => 'Formulir Pendaftaran SMP',
                'amount' => 350000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => $unitSd,
                'name' => 'Biaya Buku Cambridge',
                'amount' => 1400000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => $unitSmp,
                'name' => "Uang Gedung (Musa'adah)",
                'amount' => 15000000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catAdmin,
                'spmb_unit_id' => $unitSmp,
                'name' => 'Biaya Seragam',
                'amount' => 1500000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catTambahan,
                'spmb_unit_id' => $unitPaud,
                'name' => 'TPA',
                'amount' => 1400000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catTambahan,
                'spmb_unit_id' => $unitPaud,
                'name' => 'TPQ',
                'amount' => 1500000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catTambahan,
                'spmb_unit_id' => $unitSd,
                'name' => 'TPA',
                'amount' => 1400000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catTambahan,
                'spmb_unit_id' => $unitSd,
                'name' => 'TPQ',
                'amount' => 1500000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catTambahan,
                'spmb_unit_id' => $unitSmp,
                'name' => 'TPA',
                'amount' => 1400000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
            [
                'spmb_fee_category_id' => $catTambahan,
                'spmb_unit_id' => $unitSmp,
                'name' => 'TPQ',
                'amount' => 1500000,
                'payment_gateway' => ['winpay'],
                'is_active' => 1,
            ],
        ];
        foreach ($fees as $fee) {
            SpmbFee::updateOrCreate(
                ['spmb_unit_id' => $fee['spmb_unit_id'], 'name' => $fee['name']],
                $fee
            );
        }

        // 3. Pivot spmb_fee_category_unit
        if (Schema::hasTable('spmb_fee_category_unit')) {
            DB::table('spmb_fee_category_unit')->truncate();
            $pivot = [
                ['spmb_fee_category_id' => $catTambahan, 'spmb_unit_id' => $unitPaud],
                ['spmb_fee_category_id' => $catTambahan, 'spmb_unit_id' => $unitSd],
                ['spmb_fee_category_id' => $catTambahan, 'spmb_unit_id' => $unitSmp],
                ['spmb_fee_category_id' => $catAdmin, 'spmb_unit_id' => $unitPaud],
                ['spmb_fee_category_id' => $catAdmin, 'spmb_unit_id' => $unitSd],
                ['spmb_fee_category_id' => $catAdmin, 'spmb_unit_id' => $unitSmp],
                ['spmb_fee_category_id' => $catFormulir, 'spmb_unit_id' => $unitPaud],
                ['spmb_fee_category_id' => $catFormulir, 'spmb_unit_id' => $unitSd],
                ['spmb_fee_category_id' => $catFormulir, 'spmb_unit_id' => $unitSmp],
            ];
            DB::table('spmb_fee_category_unit')->insert($pivot);
        }
    }
}
