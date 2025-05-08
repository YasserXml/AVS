<?php

namespace App\Http\Controllers;

use App\Mail\NewUserRegistrationEmail;
use App\Models\User;
use App\Notifications\AdminNewUserNotification;
use App\Services\AdminNotificationService;
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

            $isNewUser = false;

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
                        // Otomatis verifikasi email dan admin jika login dengan Google
                        'email_verified_at' => now(),
                        'admin_verified' => true,
                    ]);
                } else {
                    // Jika user tidak ditemukan sama sekali, buat user baru
                    $isNewUser = true;

                    $user = User::create([
                        'name' => $socialiteUser->getName(),
                        'email' => $socialiteUser->getEmail(),
                        'email_verified_at' => now(), // Mark email as verified since it's coming from a provider
                        'password' => Hash::make(Str::random(16)),
                        'provider' => $provider,
                        'provider_id' => $socialiteUser->getId(),
                        'provider_token' => $socialiteUser->token,
                        'provider_refresh_token' => $socialiteUser->refreshToken ?? null,
                        'admin_verified' => true, // Otomatis verifikasi admin untuk akun Google
                    ]);

                    // Assign default user role if using Filament Shield
                    if (class_exists(Role::class)) {
                        $defaultRole = Role::where('name', 'user')->first();
                        if ($defaultRole) {
                            $user->assignRole($defaultRole);
                        }
                    }

                    // Kirim notifikasi ke admin
                    AdminNotificationService::sendNewUserRegisteredNotification($user, true);

                    // Berikan pesan ke user
                    Notification::make()
                        ->title('Pendaftaran Berhasil')
                        ->body('Akun Anda berhasil didaftarkan dan sudah terverifikasi. Anda dapat langsung login.')
                        ->success()
                        ->send();
                }
            } else {
                // Update status verifikasi jika user ditemukan
                if (!$user->admin_verified) {
                    $user->update([
                        'admin_verified' => true,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                }
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
}
