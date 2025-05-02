<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Handle the incoming request.
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
                        'provider_refresh_token' => $socialiteUser->refreshToken,
                    ]);
                } else {
                    // Jika user tidak ditemukan sama sekali, buat user baru
                    $user = User::create([
                        'name' => $socialiteUser->getName(),
                        'email' => $socialiteUser->getEmail(),
                        'email_verified_at' => now(),
                        'password' => Hash::make(Str::random(16)),
                        'provider' => $provider,
                        'provider_id' => $socialiteUser->getId(),
                        'provider_token' => $socialiteUser->token,
                        'provider_refresh_token' => $socialiteUser->refreshToken,
                    ]);
                }
            }
            
            // Login user
            Auth::login($user);
            
            // Redirect ke dashboard
            return redirect()->intended(Filament::getHomeUrl());
            
        } catch (\Exception $e) {
            // Handle error
            Notification::make()
                ->title('Login gagal')
                ->body('Terjadi kesalahan saat login dengan ' . ucfirst($provider))
                ->danger()
                ->send();
                
            return redirect()->route('filament.admin.auth.login');
        }
    }
}