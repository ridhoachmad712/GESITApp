<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Akun admin awal. Password sementara WAJIB diganti setelah login pertama.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@manajemen-feb.unm.ac.id'],
            [
                'name' => 'Admin Prodi Manajemen',
                'password' => 'SiarsipUNM#2026',
                'role' => User::ROLE_ADMIN,
                'identity_number' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
