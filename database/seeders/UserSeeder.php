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
            'user_divisi_manager_hrd' => 'User Divisi Manager HRD',
            'user_divisi_hrd_ga' => 'User Divisi HRD & GA',
            'user_divisi_keuangan' => 'User Divisi Keuangan',
            'user_divisi_software' => 'User Divisi Software',
            'user_divisi_purchasing' => 'User Divisi Purchasing',
            'user_divisi_elektro' => 'User Divisi Elektro',
            'user_divisi_r&d' => 'User Divisi R&D',
            'user_divisi_3d' => 'User Divisi 3D',
            'user_divisi_mekanik' => 'User Divisi Mekanik',
            'user_divisi_pmo' => 'User Divisi PMO',
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
