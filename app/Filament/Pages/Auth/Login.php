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
use Filament\Support\Enums\IconPosition;
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
                // Add a custom view component to display the social login options// Make it full width for responsive design
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
            ->label('Alamat Email')
            ->email()
            ->placeholder('Masukkan email Anda')
            ->required()
            ->autocomplete('email')
            ->autofocus()
            ->prefixIcon('heroicon-o-envelope')
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi')
            ->password()
            ->placeholder('Masukkan kata sandi Anda')
            ->required()
            ->revealable()
            ->prefixIcon('heroicon-o-lock-closed')
            ->autocomplete('current-password')
            ->extraInputAttributes(['tabindex' => 2]);
    }
    
    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Ingat saya');
    }

    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();
        
        // Modifikasi tombol login untuk tampilan yang lebih menarik
        if (isset($actions[0])) {
            $actions[0]->label('Masuk Sekarang')
                ->icon('heroicon-o-arrow-right-circle')
                ->iconPosition(IconPosition::After)
                ->color('primary')
                ->size('lg')
                ->extraAttributes([
                    'class' => 'w-full md:w-auto',
                ]);
        }
        
        return $actions;
    }


    /**
     * @return string|null
     */
    protected function getGuard()
    {
        return Filament::getAuthGuard();
    }

    /**
     * Get the authentication guard.
     */
    protected function getAuthGuard()
    {
        return auth()->guard($this->getGuard());
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();
        
        // Menggunakan Auth facade untuk attempt login dengan guard yang benar
        if (! auth()->guard($this->getGuard())->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ], $data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        $user = auth()->guard($this->getGuard())->user();
        
        // Add null check to avoid "Attempt to read property on null" error
        if (!$user) {
            throw ValidationException::withMessages([
                'data.email' => 'Gagal melakukan autentikasi user.',
            ]);
        }

        // Periksa apakah pengguna sudah diverifikasi oleh admin
        if (!$user->admin_verified) {
            // Logout user
            auth()->guard($this->getGuard())->logout();

            Notification::make()
                ->title('Akun Belum Diverifikasi')
                ->body('Akun Anda belum diverifikasi oleh admin. Silakan tunggu email konfirmasi atau hubungi administrator.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'Akun Anda belum diverifikasi oleh admin.',
            ]);
        }

        // Periksa apakah email sudah diverifikasi (jika diperlukan)
        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail() && config('filament.auth.require_email_verification', false)) {
            auth()->guard($this->getGuard())->logout();

            Notification::make()
                ->title('Email Belum Diverifikasi')
                ->body('Anda harus memverifikasi email Anda sebelum dapat login. Silakan cek email Anda untuk link verifikasi.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'Anda harus memverifikasi email Anda sebelum dapat login.',
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}