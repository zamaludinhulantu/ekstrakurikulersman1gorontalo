<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => '11111111',
                'role' => User::ROLE_SUPER_ADMIN,
                'phone' => '0800000000',
                'address' => 'Ruang Server Sekolah',
                'is_active' => true,
            ]
        );
    }
}
