<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles for users
        $userRoles = [
            'divisi_manager_hrd',
            'divisi_hrd',
            'divisi_keuangan',
            'divisi_software',
            'divisi_purchasing',
            'divisi_elektro',
            'divisi_r&d',
            'divisi_3d',
            'divisi_mekanik',
            'divisi_pmo',
        ];

        foreach ($userRoles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web'
            ]);
        }

        // Create roles for kepala divisi
        $kepalaRoles = [
            'kepala_divisi_hrd',
            'kepala_divisi_keuangan',
            'kepala_divisi_software',
            'kepala_divisi_purchasing',
            'kepala_divisi_elektro',
            'kepala_divisi_r&d',
            'kepala_divisi_3d',
            'kepala_divisi_mekanik',
            'kepala_divisi_pmo',
        ];

        foreach ($kepalaRoles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web'
            ]);
        }

        // Create roles for direktur
        $direkturRoles = [
            'direktur_utama',
            'direktur_teknologi',
            'direktur_produk',
            'direktur_project',
            'direktur_keuangan',
            'direktur_bisnis_marketing',
        ];

        foreach ($direkturRoles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
            
            $this->command->info("Role {$roleName} berhasil dibuat/diperbarui.");
        }

        // Berikan semua permissions kepada role direktur
        $this->assignAllPermissionsToDirectors();
    }

    /**
     * Berikan semua permissions kepada role direktur
     */
    private function assignAllPermissionsToDirectors(): void
    {
        // Ambil semua permissions yang tersedia
        $allPermissions = Permission::all();

        if ($allPermissions->count() == 0) {
            $this->command->warn('Belum ada permissions yang tersedia.');
            $this->command->warn('Jalankan perintah berikut untuk generate permissions:');
            $this->command->warn('php artisan shield:generate --all');
            return;
        }

        // Daftar role direktur
        $direkturRoles = [
            'direktur_utama',
            'direktur_teknologi',
            'direktur_produk',
            'direktur_project',
            'direktur_keuangan',
            'direktur_bisnis_marketing',
        ];

        // Assign semua permissions ke setiap role direktur
        foreach ($direkturRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            
            if ($role) {
                // Sinkronkan semua permissions ke role direktur
                $role->syncPermissions($allPermissions);
                
                $this->command->info("✅ {$allPermissions->count()} permissions berhasil diberikan ke role {$roleName}");
            }
        }

        $this->command->info('');
        $this->command->info('=== ROLE DIREKTUR BERHASIL DIKONFIGURASI ===');
        $this->command->info('Semua role direktur sekarang memiliki akses penuh seperti super admin.');
        $this->command->info('Total permissions yang diberikan: ' . $allPermissions->count());
        $this->command->warn('CATATAN: Pastikan untuk menjalankan php artisan shield:generate --all jika ada resource baru.');
    }
}
