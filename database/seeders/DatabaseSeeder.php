<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SpmbUnitAndGradeSeeder::class,
            UserSeeder::class,
            SpmbClassProgramAndServiceSeeder::class,
            SpmbPeriodWaveTypeSeeder::class,
            SpmbFeeCategoryAndFeeSeeder::class,
            PaymentGatewayAndChannelSeeder::class,
            SpmbAgreementTemplateSeeder::class,
            SpmbFormStepAndFieldSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
