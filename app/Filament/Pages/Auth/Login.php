<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
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
use Filament\Support\Enums\MaxWidth;
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
            ])
            ->columns([
                'default' => 1,
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
            ])
            ->statePath('data');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email / Nama Pengguna')
            ->placeholder('Masukkan email atau nama pengguna Anda')
            ->required()
            ->autocomplete('email')
            ->autofocus()
            ->prefixIcon('heroicon-o-user')
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
            $actions[0]->label('Masuk')
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

    protected function getGuard(): string
    {
        return Filament::getAuthGuard();
    }

    protected function getAuthGuard()
    {
        return auth()->guard($this->getGuard());
    }

    public function authenticate(): ?LoginResponse
    {
        // Rate limiting untuk mencegah brute force attack
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

        // Menentukan apakah input adalah email atau username
        $loginField = filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        // Mencoba melakukan autentikasi
        if (! auth()->guard($this->getGuard())->attempt([
            $loginField => $data['email'],
            'password' => $data['password'],
        ], $data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'data.email' => 'Email/username atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        $user = $this->getAuthGuard()->user();

        // Pastikan user berhasil diambil
        if (!$user) {
            auth()->guard($this->getGuard())->logout();
            throw ValidationException::withMessages([
                'data.email' => 'Terjadi kesalahan saat mengautentikasi pengguna.',
            ]);
        }

        // Validasi 1: Periksa apakah akun sudah diverifikasi oleh admin
        if (!$user->admin_verified) {
            auth()->guard($this->getGuard())->logout();

            Notification::make()
                ->title('Akun Belum Diverifikasi Admin')
                ->body('Akun Anda belum diverifikasi oleh administrator. Silakan tunggu konfirmasi dari admin atau hubungi tim support.')
                ->danger()
                ->duration(8000)
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'Akun Anda belum diverifikasi oleh admin. Silakan hubungi administrator.',
            ]);
        }

        // Validasi 2: Periksa apakah email sudah diverifikasi
        if (is_null($user->email_verified_at)) {
            auth()->guard($this->getGuard())->logout();

            Notification::make()
                ->title('Email Belum Diverifikasi')
                ->body('Anda harus menunggu email Anda diverifikasi terlebih dahulu oleh administrator sebelum dapat masuk')
                ->warning()
                ->duration(8000)
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'Email Anda belum diverifikasi',
            ]);
        }

        // Validasi 3: Periksa apakah akun masih aktif (tidak soft deleted)
        if ($user->deleted_at !== null) {
            auth()->guard($this->getGuard())->logout();

            Notification::make()
                ->title('Akun Tidak Aktif')
                ->body('Akun Anda telah dinonaktifkan. Silakan hubungi administrator untuk informasi lebih lanjut.')
                ->danger()
                ->duration(8000)
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ]);
        }

        // Validasi tambahan: Periksa jika ada method hasVerifiedEmail() dari Laravel
        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            auth()->guard($this->getGuard())->logout();

            Notification::make()
                ->title('Verifikasi Email Diperlukan')
                ->body('Silakan verifikasi email Anda terlebih dahulu melalui link yang telah dikirim ke email Anda.')
                ->warning()
                ->duration(8000)
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'Silakan verifikasi email Anda terlebih dahulu.',
            ]);
        }

        // Jika semua validasi berhasil, regenerate session untuk keamanan
        session()->regenerate();

        // Tampilkan notifikasi sukses
        Notification::make()
            ->title('Login Berhasil')
            ->body("Selamat datang , {$user->name}!")
            ->success()
            ->duration(5000)
            ->send();

        return app(LoginResponse::class);
    }

    /**
     * Method untuk mengirim ulang email verifikasi (opsional)
     */
    public function resendEmailVerification()
    {
        $data = $this->form->getState();
        $loginField = filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $user = User::where($loginField, $data['email'])->first();

        if ($user && is_null($user->email_verified_at)) {
            $user->sendEmailVerificationNotification();

            Notification::make()
                ->title('Email Verifikasi Dikirim')
                ->body('Link verifikasi email telah dikirim ulang ke email Anda.')
                ->success()
                ->send();
        }
    }
}
