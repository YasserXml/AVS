<?php

namespace App\Filament\Pages\Auth;

use App\Mail\UserVerificationRequestEmail;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Http\Responses\Auth\Contracts\EmailVerificationResponse;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// class EmailVerification extends BaseEmailVerificationPrompt
// {
//     use WithRateLimiting;

//     /**
//      * Halaman verifikasi email tidak perlu menampilkan form
//      * Kita bisa redirect langsung ke halaman dashboard/home
//      */
//     public function mount(): void
//     {
//         // Jika user sudah login dan belum verifikasi email
//         if (filament()->auth()->check() && ! filament()->auth()->user()->hasVerifiedEmail()) {
//             $this->sendAdminVerificationEmail();
            
//             Notification::make()
//                 ->title('Permohonan Verifikasi Terkirim')
//                 ->body('Permohonan verifikasi email telah dikirim ke admin. Silakan tunggu hingga akun Anda diverifikasi.')
//                 ->success()
//                 ->send();
//         }
//     }

//     /**
//      * Mengirim email verifikasi ke admin
//      */
//     protected function sendAdminVerificationEmail(): void
//     {
//         try {
//             $user = filament()->auth()->user();
            
//             if (!$user) {
//                 return;
//             }
            
//             // Cari admin menggunakan Filament Shield role
//             $adminUsers = User::whereHas('roles', fn ($query) => 
//                 $query->whereIn('name', ['super_admin', 'admin'])
//             )->get();
            
//             if ($adminUsers->isEmpty()) {
//                 Log::warning('Tidak ada admin yang ditemukan untuk verifikasi email');
                
//                 // Sebagai fallback, kirim ke email yang didefinisikan dalam konfigurasi
//                 $fallbackEmail = config('mail.admin_email', 'admin@example.com');
//                 Log::info('Mengirim permohonan verifikasi ke email fallback: ' . $fallbackEmail);
                
//                 Mail::to($fallbackEmail)->send(new UserVerificationRequestEmail($user));
//                 return;
//             }
    
//             Log::info('Menemukan ' . $adminUsers->count() . ' admin untuk verifikasi email');
    
//             // Kirim permintaan verifikasi ke setiap admin
//             foreach ($adminUsers as $admin) {
//                 try {
//                     Log::info('Mencoba mengirim permintaan verifikasi ke admin: ' . $admin->email);
                    
//                     Mail::to($admin->email)->send(new UserVerificationRequestEmail($user));
                    
//                     Log::info('Permintaan verifikasi berhasil dikirim ke: ' . $admin->email);
//                 } catch (\Exception $adminException) {
//                     Log::error('Gagal mengirim permintaan verifikasi ke admin: ' . $admin->email . '. Error: ' . $adminException->getMessage());
//                 }
//             }
//         } catch (\Exception $e) {
//             Log::error('Error dalam sendAdminVerificationEmail: ' . $e->getMessage());
//             Log::error('Stack trace: ' . $e->getTraceAsString());
//         }
//     }

//     /**
//      * Mengirim ulang permintaan verifikasi
//      */
//     public function resendNotification(): void
//     {
//         try {
//             $this->rateLimit(2);
//         } catch (TooManyRequestsException $exception) {
//             Notification::make()
//                 ->title('Batas percobaan tercapai')
//                 ->body('Terlalu banyak permintaan. Silakan coba lagi dalam ' . ceil($exception->secondsUntilAvailable / 60) . ' menit.')
//                 ->danger()
//                 ->send();

//             return;
//         }

//         $user = Auth::user();

//         if (! $user) {
//             return;
//         }

//         // Kirim permintaan verifikasi ke admin
//         $this->sendAdminVerificationEmail();

//         Notification::make()
//             ->title('Permintaan verifikasi telah dikirim')
//             ->body('Permintaan verifikasi telah dikirim ke admin. Silakan tunggu konfirmasi.')
//             ->success()
//             ->send();
//     }

//     /**
//      * Memverifikasi email user (ini akan dipanggil oleh admin, bukan user)
//      */
//     public function verify(): ?EmailVerificationResponse
//     {
//         $user = Auth::user();

//         if (! $user) {
//             return null;
//         }

//         if ($user->hasVerifiedEmail()) {
//             return app(EmailVerificationResponse::class);
//         }

//         $user->markEmailAsVerified();
        
//         Notification::make()
//             ->title('Email berhasil diverifikasi')
//             ->body('Email Anda berhasil diverifikasi oleh admin.')
//             ->success()
//             ->send();

//         return app(EmailVerificationResponse::class);
//     }
// }