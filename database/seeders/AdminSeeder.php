<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role admin sudah ada (jika belum ada, buat)
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'name' => 'admin',
                'guard_name' => 'web'
            ]
        );

        // Buat atau update user admin
        $admin = User::updateOrCreate(
            ['email' => 'afihirkam@gmail.com'],
            [
                'name' => 'Admin User',
                'email' => 'afihirkam@gmail.com',
                'password' => Hash::make('123'),
                'email_verified_at' => now(),
                'admin_verified' => true,
            ]
        );

        // Assign role admin ke user
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info('=== ADMIN BERHASIL DIBUAT ===');
        $this->command->info('Nama: Admin User');
        $this->command->info('Email: afihirkam@gmail.com');
        $this->command->info('Password: 123');
        $this->command->info('Role: admin');
        $this->command->warn('PENTING: Ganti password setelah login pertama kali!');
    }
}
