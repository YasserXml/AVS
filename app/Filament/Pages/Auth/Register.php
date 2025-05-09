<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Notifications\NewUserRegistered;
use App\Services\AdminNotificationService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as FilamentRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class Register extends FilamentRegister
{
    use WithRateLimiting;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                // Tambahkan social login buttons disini juga
                View::make('pages.auth.social-login-buttons')
                    ->columnSpanFull(),
            ])
            ->columns([
                'default' => 1,
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
            ]) // Make form responsive
            ->statePath('data');
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Nama Pengguna')
            ->placeholder('Tuliskan Nama Pengguna Anda')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->placeholder('Tuliskan Email Anda')
            ->required()
            ->maxLength(255)
            ->unique(table: User::class, column: 'email');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi')
            ->password()
            ->placeholder('Tuliskan kata sandi Anda')
            ->required()
            ->minLength(8)
            ->confirmed();
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('password_confirmation')
            ->label('Konfirmasi Kata Sandi')
            ->password()
            ->placeholder('Tulis ulang kata sandi Anda')
            ->required()
            ->minLength(8)
            ->dehydrated(false);
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            // Rate limiting notification code...
            return null;
        }

        $data = $this->form->getState();

        $data['password'] = Hash::make($data['password']);
        // Set admin_verified ke false secara default
        $data['admin_verified'] = false;

        $user = User::create($data);

        // Assign default role 'user' ke pengguna baru
        $user->assignRole('user');

        // We still trigger the Registered event for other listeners
        event(new Registered($user));

        // Kirim notifikasi ke admin
        // AdminNotificationService::sendNewUserRegisteredNotification($user);

        // Tampilkan pesan ke pengguna (tanpa menyebut verifikasi email)
        Notification::make()
            ->title('Pendaftaran Berhasil')
            ->body('Akun Anda telah terdaftar tetapi memerlukan verifikasi dari admin sebelum dapat digunakan.')
            ->success()
            ->send();

        // Redirect to login page
        return app(RegistrationResponse::class);
    }

    protected function redirectPath(string $redirectRoute): RegistrationResponse
    {
        return app(RegistrationResponse::class);
    }
}