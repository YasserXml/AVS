<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TrySeeder extends Seeder
{
    public function run(): void
    {
        // Buat atau update user super admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'arkihirkam@gmail.com'],
            [
                'name' => 'Yas',
                'email' => 'arkihirkam@gmail.com',
                'password' => Hash::make('123'),
                'email_verified_at' => now(),
                'admin_verified' => true,
            ]
        );

        $this->command->info('=== SUPER ADMIN BERHASIL DIBUAT ===');
        $this->command->info('Nama: Yasser');
        $this->command->info('Email: arkihirkam@gmail.com');
        $this->command->info('Password: 123');
        $this->command->info('Role: super_admin');
        $this->command->warn('PENTING: Ganti password setelah login pertama kali!');
    }
}
