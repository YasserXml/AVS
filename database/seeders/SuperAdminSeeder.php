<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role super_admin jika belum ada
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin'],
            [
                'name' => 'super_admin',
                'guard_name' => 'web'
            ]
        );

        // Buat atau update user super admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'yasser@avsimulator.com'],
            [
                'name' => 'Yasser',
                'email' => 'yasser@avsimulator.com',
                'password' => Hash::make('123'),
                'email_verified_at' => now(),
                'admin_verified' => true, // Pastikan sudah terverifikasi admin
            ]
        );

        // Assign role super_admin ke user
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        // Berikan semua permission ke super_admin role
        $allPermissions = Permission::all();
        if ($allPermissions->count() > 0) {
            $superAdminRole->syncPermissions($allPermissions);
            $this->command->info($allPermissions->count() . ' permissions berhasil diberikan ke role super_admin.');
        } else {
            $this->command->warn('Belum ada permissions yang tersedia. Jalankan php artisan shield:generate --all setelah membuat resources.');
        }

        $this->command->info('=== SUPER ADMIN BERHASIL DIBUAT ===');
        $this->command->info('Nama: Yasser');
        $this->command->info('Email: yasser@avsimulator.com');
        $this->command->info('Password: 123');
        $this->command->info('Role: super_admin');
        $this->command->warn('PENTING: Ganti password setelah login pertama kali!');
    }
}
