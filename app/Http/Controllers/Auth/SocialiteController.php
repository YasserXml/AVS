<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminNewUserNotification;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class SocialiteController extends Controller
{
    /**
     * Handle the incoming request for socialite callback.
     */
    public function handleCallback(string $provider)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();
            
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

                }
            }
            
            // If the user hasn't verified their email yet, mark it as verified
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
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
            // Handle error
            Notification::make()
                ->title('Login gagal')
                ->body('Terjadi kesalahan saat login dengan ' . ucfirst($provider) . ': ' . $e->getMessage())
                ->danger()
                ->send();
                
            return redirect()->route('filament.admin.auth.login');
        }
    }
}