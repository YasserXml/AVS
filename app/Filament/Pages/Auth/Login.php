<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Actions\Action;
use Filament\Pages\Auth\Login\Contracts\HasEmailVerification;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;

class Login extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent()
                    ->required()
                    ->autocomplete('username')
                    ->autofocus()
                    ->extraValidationAttributes(['email' => 'alamat email'])
                    ->customValidateUsing(function (TextInput $component, string $state, callable $fail) {
                        // Validasi email
                        if (!filter_var($state, FILTER_VALIDATE_EMAIL)) {
                            $fail('Format email tidak valid.');
                            return;
                        }

                        // Cek apakah domain email adalah gmail.com
                        $domain = explode('@', $state)[1] ?? '';
                        if ($domain !== 'gmail.com') {
                            $fail('Hanya email Google (Gmail) yang diperbolehkan.');
                            return;
                        }

                        // Verifikasi apakah email benar-benar ada
                        try {
                            $response = $this->verifyEmailExists($state);
                            if (!$response) {
                                $fail('Email tidak terdaftar di Google.');
                            }
                        } catch (\Exception $e) {
                            $fail('Terjadi kesalahan saat memverifikasi email: ' . $e->getMessage());
                        }
                    }),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    /**
     * Memeriksa apakah email benar-benar ada di Google
     * Menggunakan metode sederhana untuk pengecekan (diperlukan integrasi lebih lanjut dengan Socialite)
     */
    protected function verifyEmailExists(string $email): bool
    {
        // Metode 1: Pemeriksaan dasar menggunakan validasi dan format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Metode 2: Gunakan Socialite untuk Google Authentication
        // Catatan: Metode ini tidak sepenuhnya memeriksa keberadaan email
        // tetapi memerlukan autentikasi langsung pengguna
        
        // Untuk pemeriksaan yang lebih akurat, Anda dapat:
        // 1. Gunakan SMTP verification (perlu library tambahan)
        // 2. Implementasikan OAuth2 untuk autentikasi email

        // Di sini kita hanya mengembalikan true untuk demonstrasi
        // Implementasi sesungguhnya akan memerlukan integrasi dengan Google API
        
        return true;
    }

    /**
     * Tambahkan opsi untuk login dengan Google
     */
    protected function getEmailFormComponent(): Component
    {
        $emailInput = parent::getEmailFormComponent();

        // Menambahkan tombol "Login dengan Google" di bawah input email
        $emailInput->suffixAction(
            Action::make('loginWithGoogle')
                ->label('Login dengan Google')
                ->color('primary')
                ->icon('heroicon-o-academic-cap')
                ->url(route('socialite.redirect', ['provider' => 'google']))
        );

        return $emailInput;
    }
}