<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SpmbUnit;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'TIM DEV',
                'email' => 'superadmin@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'super_admin',
                'spmb_unit_code' => null,
            ],
            [
                'name' => 'Admin Paud',
                'email' => 'admin-paud@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'admin',
                'spmb_unit_code' => 'PAUD',
            ],
            [
                'name' => 'Admin SD',
                'email' => 'admin-sd@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'admin',
                'spmb_unit_code' => 'SD',
            ],
            [
                'name' => 'Admin SMP',
                'email' => 'admin-smp@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'admin',
                'spmb_unit_code' => 'SMP',
            ],
        ];

        foreach ($users as $user) {
            $unitId = $user['spmb_unit_code']
                ? SpmbUnit::where('code', $user['spmb_unit_code'])->first()?->id
                : null;

            User::updateOrCreate(['email' => $user['email']], [
                'name' => $user['name'],
                'password' => $user['password'],
                'role' => $user['role'],
                'spmb_unit_id' => $unitId,
            ]);
        }
    }
}
