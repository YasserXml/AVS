<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        // Reset cache permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🚀 Memulai pembuatan roles...');
        $this->command->info('');

        // Buat roles untuk divisi (berdasarkan gambar)
        $this->buatRolesDivisi();

        // Buat roles untuk kepala divisi
        $this->buatRolesKepalaDivisi();

        // Buat roles untuk direktur (direktorat)
        $this->buatRolesDirektur();

        // Berikan semua permissions kepada role direktur
        $this->berikanPermissionsKeDirektur();

        $this->command->info('');
        $this->command->info('✅ Semua roles berhasil dibuat!');
    }

    /**
     * Buat roles untuk divisi berdasarkan gambar
     */
    private function buatRolesDivisi(): void
    {
        $this->command->info('📁 Membuat roles untuk divisi...');

        $divisiRoles = [
            'divisi_manager_hrd',
            'divisi_hrd_ga',
            'divisi_purchasing',
            'divisi_keuangan',
            'divisi_rnd',
            'divisi_pmo',
            'divisi_software',
            'divisi_elektro',
            'divisi_3d',
            'divisi_mekanik',
        ];

        foreach ($divisiRoles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);

            $this->command->info("   ✓ Role {$roleName} berhasil dibuat");
        }

        $this->command->info('');
    }

    /**
     * Buat roles untuk kepala divisi
     */
    private function buatRolesKepalaDivisi(): void
    {
        $this->command->info('👨‍💼 Membuat roles untuk kepala divisi...');

        $kepalaRoles = [
            'kepala_divisi_ga',
            'kepala_divisi_keuangan',
            'kepala_divisi_rnd',
            'kepala_divisi_pmo',
            'kepala_divisi_software',
            'kepala_divisi_elektro',
            'kepala_divisi_3d',
            'kepala_divisi_mekanik',
        ];

        foreach ($kepalaRoles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);

            $this->command->info("   ✓ Role {$roleName} berhasil dibuat");
        }

        $this->command->info('');
    }

    /**
     * Buat roles untuk direktur (direktorat)
     */
    private function buatRolesDirektur(): void
    {
        $this->command->info('🏢 Membuat roles untuk direktur...');

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

            $this->command->info("   ✓ Role {$roleName} berhasil dibuat");
        }

        $this->command->info('');
    }

    /**
     * Berikan semua permissions kepada role direktur
     */
    private function berikanPermissionsKeDirektur(): void
    {
        $this->command->info('🔐 Memberikan permissions kepada direktur...');

        // Ambil semua permissions yang tersedia
        $allPermissions = Permission::all();

        if ($allPermissions->count() == 0) {
            $this->command->warn('⚠️  Belum ada permissions yang tersedia.');
            $this->command->warn('   Jalankan perintah berikut untuk generate permissions:');
            $this->command->warn('   php artisan shield:generate --all');
            return;
        }

        // Daftar role direktur yang akan mendapat semua permissions
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
                
                $this->command->info("   ✓ {$allPermissions->count()} permissions diberikan ke {$roleName}");
            }
        }

        $this->command->info('');
        $this->command->info('🎉 KONFIGURASI ROLE DIREKTUR SELESAI');
        $this->command->info("   Total permissions yang diberikan: {$allPermissions->count()}");
        $this->command->info('   Semua direktur sekarang memiliki akses penuh seperti super admin');
        
        if ($allPermissions->count() > 0) {
            $this->command->warn('💡 CATATAN: Jika menambah resource baru, jalankan:');
            $this->command->warn('   php artisan shield:generate --all');
        }
    }
}