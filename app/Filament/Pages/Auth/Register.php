<?php

namespace App\Filament\Pages\Auth;

use App\Mail\NewUserRegistrationEmail;
use App\Models\User;
use App\Notifications\AdminNewUserNotification;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class Register extends BaseRegister
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
                // Tombol login sosial
                View::make('pages.auth.social-login-buttons')
                    ->columnSpanFull()
            ])
            ->columns([
                'default' => 1,
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
            ])
            ->statePath('data');
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Nama Pengguna')
            ->placeholder('Tuliskan Nama Pengguna Anda')
            ->helperText('Nama Pengguna atau Nama Lengkap Anda')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->placeholder('Tuliskan Email Anda')
            ->helperText('Email Anda akan digunakan untuk login. Admin akan memverifikasi akun Anda.')
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(table: User::class);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi')
            ->placeholder('Tuliskan kata sandi Anda')
            ->helperText('Kata sandi Anda harus terdiri dari minimal 8 karakter')
            ->password()
            ->required()
            ->rule(Password::default())
            ->dehydrateStateUsing(fn($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute('kata sandi');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Konfirmasi Kata Sandi')
            ->placeholder('Tulis ulang kata sandi Anda')
            ->helperText('Konfirmasi kata sandi Anda')
            ->password()
            ->required()
            ->dehydrated(false);
    }

    protected function getFormState(): array
    {
        return [
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'password' => $this->data['password'],
            'admin_verified' => false, // User baru belum diverifikasi admin
        ];
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Batas percobaan tercapai')
                ->body('Terlalu banyak percobaan pendaftaran. Silakan coba lagi dalam ' . $exception->secondsUntilAvailable . ' detik.')
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();
        $user = User::create($data);

        // Assign default role (user) menggunakan Filament Shield
        $defaultRole = Role::where('name', 'user')->first();
        if ($defaultRole) {
            $user->assignRole($defaultRole);
        }

        event(new Registered($user));

        // Kirim email ke admin
        $this->notifyAdmins($user);

        Notification::make()
            ->title('Pendaftaran Berhasil')
            ->body('Akun Anda berhasil didaftarkan. Silakan tunggu verifikasi dari admin sebelum dapat login.')
            ->success()
            ->send();

        return app(RegistrationResponse::class);
    }

    protected function notifyAdmins(User $newUser): void
    {
        // Cari admin menggunakan Filament Shield role
        $adminEmails = User::whereHas('roles', fn ($query) => 
            $query->whereIn('name', ['super_admin', 'admin'])
        )->pluck('email')->toArray();

        $submitter = Filament::auth()->user() ?? $newUser; // Jika tidak ada user yang login, gunakan user baru

        // Kirim email ke setiap admin
        foreach ($adminEmails as $email) {
            Mail::to($email)->queue(new NewUserRegistrationEmail($newUser, $submitter));
        }
    }
}