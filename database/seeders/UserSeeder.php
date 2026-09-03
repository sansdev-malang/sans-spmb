<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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
                'id' => 2,
                'name' => 'Admin Paud',
                'email' => 'admin-paud@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'admin',
                'spmb_unit_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'TIM DEV',
                'email' => 'superadmin@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'super_admin',
                'spmb_unit_id' => null,
            ],
            [
                'id' => 6,
                'name' => 'Admin SD',
                'email' => 'admin-sd@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'admin',
                'spmb_unit_id' => 2,
            ],
            [
                'id' => 7,
                'name' => 'Admin SMP',
                'email' => 'admin-smp@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'admin',
                'spmb_unit_id' => 3,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], [
                'name' => $user['name'],
                'password' => $user['password'],
                'role' => $user['role'],
                'spmb_unit_id' => $user['spmb_unit_id'],
            ]);
        }
    }
}
