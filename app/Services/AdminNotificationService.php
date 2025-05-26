<?php

namespace App\Services;

use App\Mail\NewUserRegistrationMail;
use App\Mail\PengajuanBarangMail;
use App\Mail\UserVerified;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AdminNotificationService
{
    /**
     * Kirim notifikasi email ke admin dan super admin untuk pengajuan barang
     * 
     * @param array $pengajuanItems
     * @param User $pengaju
     * @return void
     */
    public static function sendPengajuanNotification($pengajuanItems, User $pengaju)
    {
        try {
            // Ambil semua user dengan role admin dan super_admin
            $adminUsers = User::whereHas('roles', fn ($query) => 
                $query->whereIn('name', ['super_admin', 'admin'])
            )->get();
            
            // Fallback: jika tidak ada role yang ditemukan, cari berdasarkan email atau user tertentu
            if ($adminUsers->isEmpty()) {
                //menambahkan email admin secara manual di sini
                $fallbackAdmins = User::whereIn('email', [
                    'admin@example.com',
                    'superadmin@example.com'
                ])->get();
                $adminUsers = $adminUsers->merge($fallbackAdmins);
            }
            
            // Hapus duplikasi berdasarkan email
            $adminUsers = $adminUsers->unique('email');
            
            if ($adminUsers->isNotEmpty()) {
                $emailCount = 0;
                foreach ($adminUsers as $admin) {
                    try {
                        // Kirim notifikasi ke super admin dan admin
                        Notification::make()
                            ->title('Pengajuan Barang Baru')
                            ->icon('heroicon-o-shopping-cart')
                            ->iconColor('warning')
                            ->body("📦 {$pengaju->name} telah mengajukan " . count($pengajuanItems) . " barang dan menunggu persetujuan.")
                            ->actions([
                                Action::make('review')
                                    ->label('Review Pengajuan')
                                    ->url(route('filament.admin.resources.pengajuan.index'))
                                    ->button(),
                            ])
                            ->sendToDatabase($admin);
                        
                        // Kirim email
                        Mail::to($admin->email)->queue(new PengajuanBarangMail($pengajuanItems, $pengaju));
                        $emailCount++;
                        
                        Log::info("Email pengajuan berhasil dikirim ke: {$admin->email}");
                    } catch (\Exception $e) {
                        Log::error("Gagal mengirim email ke {$admin->email}: " . $e->getMessage());
                    }
                }
                
                Log::info("Total email pengajuan yang berhasil dikirim: {$emailCount}");
            } else {
                Log::warning('Tidak ada admin yang ditemukan untuk menerima notifikasi pengajuan');
            }
            
        } catch (\Exception $e) {
            Log::error('Error dalam mengirim notifikasi pengajuan: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi pendaftaran pengguna baru ke semua admin
     *
     * @param User $newUser
     * @return void
     */
    public static function sendNewUserRegistrationNotifications(User $newUser)
    {
        // Ambil semua admin
        $adminUsers = User::whereHas('roles', fn ($query) => 
            $query->whereIn('name', ['super_admin', 'admin'])
        )->get();
        
        // Buat URL verifikasi
        $verificationUrl = route('admin.verify-user', [
            'user' => $newUser->id,
            'hash' => sha1($newUser->email),
        ]);
        
        foreach ($adminUsers as $admin) {
            // Kirim notifikasi Filament
            Notification::make()
                ->title('Pendaftaran Pengguna Baru')
                ->icon('heroicon-o-user-plus')
                ->iconColor('info')
                ->body("👤 {$newUser->name} telah mendaftar dan menunggu verifikasi.")
                ->sendToDatabase($admin);
            
            // Kirim email
            Mail::to($admin->email)->queue(new NewUserRegistrationMail($newUser, $admin));
        }
    }
    
    /**
     * Kirim notifikasi verifikasi pengguna ke pengguna yang bersangkutan
     *
     * @param User $user
     * @return void
     */
    public static function sendUserVerifiedNotification(User $user)
    {
        // Kirim notifikasi Filament
        Notification::make()
            ->title('Akun Anda Telah Diverifikasi')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->body('Selamat! Akun Anda telah berhasil diverifikasi oleh administrator.')
            ->actions([
                Action::make('login')
                    ->label('Login Sekarang')
                    ->url(route('filament.admin.auth.login'))
                    ->button(),
            ])
            ->sendToDatabase($user);
        
        // Kirim email
        Mail::to($user->email)->queue(new UserVerified($user));
    }
    
    /**
     * Ambil daftar email admin dan super admin
     * 
     * @return array
     */
    public static function getAdminEmails(): array
    {
        try {
            $emails = [];
            
            // Ambil email dari super admin
            $superAdmins = User::role('super_admin')->pluck('email')->toArray();
            $emails = array_merge($emails, $superAdmins);
            
            // Ambil email dari admin
            $admins = User::role('admin')->pluck('email')->toArray();
            $emails = array_merge($emails, $admins);
            
            // Hapus duplikasi
            $emails = array_unique($emails);
            
            return $emails;
        } catch (\Exception $e) {
            Log::error('Error dalam mengambil email admin: ' . $e->getMessage());
            return [];
        }
    }
}