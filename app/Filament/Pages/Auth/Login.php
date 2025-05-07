<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\View\View as IlluminateViewView;

class Login extends BaseLogin
{
    use WithRateLimiting;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                // Add a custom view component to display the social login options
                View::make('pages.auth.social-login-buttons')
                    ->columnSpanFull(), // Make it full width for responsive design
            ])
            ->columns([
                'default' => 1,
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
            ]) // Make form responsive
            ->statePath('data');
    }
     
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->placeholder('Tuliskan Email Anda')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->placeholder('Tuliskan Password Anda')
            ->required()
            ->autocomplete('current-password')
            ->extraInputAttributes(['tabindex' => 2]);
    }
    
    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Ingat saya');
    }

    public function authenticate(): LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Batas percobaan tercapai')
                ->body('Terlalu banyak percobaan login. Silakan coba lagi dalam ' . ceil($exception->secondsUntilAvailable / 60) . ' menit.')
                ->danger()
                ->send();

            return $this->response();
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentials($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        // Periksa verifikasi admin
        if (!$user->admin_verified) {
            Filament::auth()->logout();
            
            Notification::make()
                ->title('Akun Belum Diverifikasi')
                ->body('Akun Anda masih dalam proses verifikasi oleh admin. Kami akan memberi tahu Anda melalui email saat akun Anda telah diverifikasi.')
                ->danger()
                ->send();
            
            return $this->response();
        }

        session()->regenerate();
        return app(LoginResponse::class);
    }

    /**
     * Memeriksa masalah verifikasi user
     * 
     * @param \App\Models\User $user
     * @return array
     */
    protected function checkVerificationIssues($user): array
    {
        $issues = [];
        
        // Jika fitur verifikasi email diaktifkan (cek di konfigurasi app)
        if (config('auth.verify_email', false) && !$user->hasVerifiedEmail()) {
            // Kirim ulang email verifikasi
            $user->sendEmailVerificationNotification();
            
            $issues[] = [
                'title' => 'Email Belum Terverifikasi',
                'body' => 'Silakan verifikasi email Anda terlebih dahulu. Kami telah mengirimkan ulang tautan verifikasi ke email Anda.'
            ];
        }

        // Jika fitur verifikasi admin diaktifkan
        if (config('auth.admin_verification', false) && !$user->admin_verified) {
            $issues[] = [
                'title' => 'Akun Belum Diverifikasi Admin',
                'body' => 'Akun Anda masih dalam proses verifikasi oleh admin. Kami akan memberi tahu Anda melalui email saat akun Anda telah diverifikasi.'
            ];
        }
        
        return $issues;
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'Email atau password yang Anda masukkan salah. Silakan coba lagi.',
        ]);
    }

    protected function getCredentials(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
}