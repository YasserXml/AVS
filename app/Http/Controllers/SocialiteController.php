<?php
namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect ke provider OAuth.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Callback dari provider OAuth.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback($provider)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();
            
            // Cek apakah email dari Google
            $domain = explode('@', $socialiteUser->getEmail())[1] ?? '';
            if ($domain !== 'gmail.com') {
                return redirect()->route('filament.auth.login')
                    ->with('error', 'Hanya email Google yang diperbolehkan.');
            }
            
            // Cari user berdasarkan provider_id dan provider
            $user = User::where([
                'provider' => $provider,
                'provider_id' => $socialiteUser->getId(),
            ])->first();
            
            // Jika user belum ada, buat user baru
            if (!$user) {
                // Cari berdasarkan email
                $user = User::where('email', $socialiteUser->getEmail())->first();
                
                // Jika user dengan email tersebut ada, update provider info
                if ($user) {
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialiteUser->getId(),
                        'provider_token' => $socialiteUser->token,
                    ]);
                } else {
                    // Buat user baru
                    $user = User::create([
                        'name' => $socialiteUser->getName(),
                        'email' => $socialiteUser->getEmail(),
                        'provider' => $provider,
                        'provider_id' => $socialiteUser->getId(),
                        'provider_token' => $socialiteUser->token,
                        'email_verified_at' => now(),
                    ]);
                }
            }
            
            // Login user
            Auth::login($user);
            
            // Redirect ke dashboard
            return redirect()->intended(config('filament.home_url', '/'));
            
        } catch (Exception $e) {
            return redirect()->route('filament.auth.login')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}