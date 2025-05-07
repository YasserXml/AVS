<?php

namespace App\Http\Controllers;

use App\Mail\NewUserRegistrationEmail;
use App\Models\User;
use App\Notifications\AdminNewUserNotification;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class SocialiteController extends Controller
{
    /**
     * Redirect to provider for authentication.
     */
    public function redirect(string $provider)
    {
        // Validate if the provider is supported
        if (!in_array($provider, ['google'])) {
            Notification::make()
                ->title('Provider tidak didukung')
                ->body('Saat ini hanya login dengan Google yang didukung.')
                ->danger()
                ->send();
                
            return redirect()->route('filament.admin.auth.login');
        }
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the incoming request for socialite callback.
     */
    public function handleCallback(string $provider)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();
            
            // Periksa apakah email dari domain Google
            if ($provider === 'google' && !Str::endsWith($socialiteUser->getEmail(), '@gmail.com')) {
                Notification::make()
                    ->title('Pendaftaran Gagal')
                    ->body('Hanya email Gmail yang diperbolehkan untuk pendaftaran.')
                    ->danger()
                    ->send();
                
                return redirect()->route('filament.admin.auth.login');
            }
            
            // Cari user berdasarkan provider_id dan provider
            $user = User::where([
                'provider' => $provider,
                'provider_id' => $socialiteUser->getId(),
            ])->first();
            
            // Jika user tidak ditemukan, cari berdasarkan email
            if (!$user) {
                $user = User::where('email', $socialiteUser->getEmail())->first();
                
                // Jika user sudah ada berdasarkan email, update provider details
                if ($user) {
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialiteUser->getId(),
                        'provider_token' => $socialiteUser->token,
                        'provider_refresh_token' => $socialiteUser->refreshToken ?? null,
                    ]);
                } else {
                    // Jika user tidak ditemukan sama sekali, buat user baru
                    $user = User::create([
                        'name' => $socialiteUser->getName(),
                        'email' => $socialiteUser->getEmail(),
                        'email_verified_at' => now(), // Mark email as verified since it's coming from a provider
                        'password' => Hash::make(Str::random(16)),
                        'provider' => $provider,
                        'provider_id' => $socialiteUser->getId(),
                        'provider_token' => $socialiteUser->token,
                        'provider_refresh_token' => $socialiteUser->refreshToken ?? null,
                        'admin_verified' => false, // User baru perlu verifikasi admin
                    ]);
                    
                    // Assign default user role if using Filament Shield
                    if (class_exists(Role::class)) {
                        $defaultRole = Role::where('name', 'user')->first();
                        if ($defaultRole) {
                            $user->assignRole($defaultRole);
                        }
                    }
                    
                    // Notifikasi untuk admin SAJA
                    $this->notifyAdmins($user);
                    
                    // Berikan pesan ke user
                    Notification::make()
                        ->title('Pendaftaran Berhasil')
                        ->body('Akun Anda berhasil didaftarkan. Silakan tunggu verifikasi dari admin sebelum dapat login.')
                        ->success()
                        ->send();
                        
                    return redirect()->route('filament.admin.auth.login');
                }
            }
            
            // Cek apakah user sudah diverifikasi admin
            if (!$user->admin_verified) {
                Notification::make()
                    ->title('Akun Belum Diverifikasi Admin')
                    ->body('Akun Anda masih dalam proses verifikasi oleh admin. Kami akan memberi tahu Anda melalui email saat akun Anda telah diverifikasi.')
                    ->danger()
                    ->send();
                    
                return redirect()->route('filament.admin.auth.login');
            }
            
            // Login user
            Auth::login($user);
            
            // Redirect ke dashboard
            return redirect()->intended(Filament::getHomeUrl());
            
        } catch (\Exception $e) {
            Log::error('Error login Socialite: ' . $e->getMessage());
            // Handle error
            Notification::make()
                ->title('Login gagal')
                ->body('Terjadi kesalahan saat login dengan ' . ucfirst($provider) . ': ' . $e->getMessage())
                ->danger()
                ->send();
                
            return redirect()->route('filament.admin.auth.login');
        }
    }
    
    /**
     * Notifikasi admin tentang pendaftaran baru
     */
    protected function notifyAdmins(User $newUser): void
    {
        try {
            // Cari admin menggunakan Filament Shield role
            $adminUsers = User::whereHas('roles', fn ($query) => 
                $query->whereIn('name', ['super_admin', 'admin'])
            )->get();
            
            if ($adminUsers->isEmpty()) {
                Log::warning('Tidak ada admin yang ditemukan untuk notifikasi pendaftaran baru');
                
                // Sebagai fallback, kirim ke email yang didefinisikan dalam konfigurasi
                $fallbackEmail = config('mail.admin_email', 'admin@example.com');
                Log::info('Mengirim notifikasi ke email fallback: ' . $fallbackEmail);
                
                Mail::to($fallbackEmail)->send(new NewUserRegistrationEmail($newUser));
                return;
            }
    
            Log::info('Menemukan ' . $adminUsers->count() . ' admin untuk notifikasi');
    
            // Kirim notifikasi ke setiap admin
            foreach ($adminUsers as $admin) {
                try {
                    Log::info('Mencoba mengirim notifikasi ke admin: ' . $admin->email);
                    
                    // Kirim email
                    Mail::to($admin->email)->send(new NewUserRegistrationEmail($newUser));
                    
                    // Notifikasi database untuk admin
                    $admin->notify(new AdminNewUserNotification($newUser));
                    
                    Log::info('Notifikasi berhasil dikirim ke: ' . $admin->email);
                } catch (\Exception $adminException) {
                    Log::error('Gagal mengirim notifikasi ke admin: ' . $admin->email . '. Error: ' . $adminException->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Error dalam notifyAdmins: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}