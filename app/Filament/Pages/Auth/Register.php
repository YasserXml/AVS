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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use BezhanSalleh\FilamentShield\Support\Utils; // Gunakan Utils dari FilamentShield

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
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->placeholder('Tuliskan Email Anda')
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
            'provider' => null, // Pendaftaran melalui form
            'provider_id' => null,
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

        // Pastikan admin_verified selalu false untuk pendaftaran baru
        $data['admin_verified'] = false;

        $user = User::create($data);

        // Assign default role (user) menggunakan Filament Shield
        try {
            $userRoleName = Utils::getPanelUserRoleName(); // Dapatkan nama role user dari Shield
            if (!$userRoleName) {
                $userRoleName = 'user'; // Fallback jika tidak berhasil mendapatkan dari Shield
            }
            
            Log::info('Mencoba assign role: ' . $userRoleName . ' ke user baru');
            $user->assignRole($userRoleName);
        } catch (\Exception $e) {
            Log::error('Gagal assign role: ' . $e->getMessage());
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
        try {
            // Cari admin menggunakan Filament Shield role
            $adminUsers = User::whereHas('roles', fn ($query) => 
                $query->whereIn('name', ['super_admin', 'admin'])
            )->get();
            
            if ($adminUsers->isEmpty()) {
                Log::warning('Tidak ada admin yang ditemukan untuk notifikasi pendaftaran baru');
                
                // Sebagai fallback, kirim ke email yang didefinisikan dalam konfigurasi
                $fallbackEmail = config('mail.admin_email', 'admin@example.com');
                Log::info('Mengirim notifikasi ke email fallback: ' . $fallbackEmail);
                
                Mail::to($fallbackEmail)->send(new NewUserRegistrationEmail($newUser));
                return;
            }
    
            Log::info('Menemukan ' . $adminUsers->count() . ' admin untuk notifikasi');
    
            // Kirim notifikasi ke setiap admin
            foreach ($adminUsers as $admin) {
                try {
                    Log::info('Mencoba mengirim notifikasi ke admin: ' . $admin->email);
                    
                    // Gunakan kelas Mail langsung untuk debugging
                    Mail::to($admin->email)->send(new NewUserRegistrationEmail($newUser));
                    
                    Log::info('Notifikasi berhasil dikirim ke: ' . $admin->email);
                } catch (\Exception $adminException) {
                    Log::error('Gagal mengirim notifikasi ke admin: ' . $admin->email . '. Error: ' . $adminException->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Error dalam notifyAdmins: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}