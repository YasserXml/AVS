<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Definisikan daftar divisi dan nama role yang sesuai
        $divisiRoles = [
            'user_sekretariat' => 'User Sekretariat',
            'user_hrd_ga' => 'User HRD & GA',
            'user_purchasing' => 'User Purchasing',
            'user_keuangan' => 'User Keuangan',
            'user_akuntansi' => 'User Akuntansi',
            'user_bisnis_marketing' => 'User Bisnis & Marketing',
            'user_system_engineer' => 'User System Engineer',
            'user_rnd' => 'User RnD',
            'user_game_programming' => 'User Game Programming',
            'user_pmo' => 'User PMO',
            'user_3d' => 'User 3D',
            'user_mekanik' => 'User Mekanik',
        ];

        // Buat role untuk setiap divisi
        foreach ($divisiRoles as $roleName => $displayName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->command->info("Role '{$roleName}' ({$displayName}) berhasil dibuat atau sudah ada.");
        }

        $this->command->info('Semua role divisi berhasil dibuat!');
    }
}
