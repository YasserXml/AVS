<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Notifications\NewUserRegistered;
use App\Services\AdminNotificationService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
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
                $this->getDivisiFormComponent(), // Tambahkan komponen pilih divisi
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
            ]) // Buat form responsif
            ->statePath('data');
    }

    // Metode untuk komponen form divisi
    protected function getDivisiFormComponent(): Component
    {
        // Definisikan opsi divisi secara hardcoded (sesuai data dari screenshot database)
        $divisiOptions = [
            'divisi_manager_hrd' => 'Manager HRD',
            'divisi_hrd_ga' => 'HRD & GA',
            'divisi_keuangan' => 'Keuangan',
            'divisi_software' => 'Software',
            'divisi_purchasing' => 'Purchasing',
            'divisi_elektro' => 'Elektro',
            'divisi_r&d' => 'R&D',
            'divisi_3d' => '3D',
            'divisi_mekanik' => 'Mekanik',
        ];
        
        return Select::make('divisi_role')
            ->label('Pilih Divisi')
            ->placeholder('Pilih Divisi Anda')
            ->searchable()
            ->preload()
            ->options($divisiOptions)
            ->required();
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
            Notification::make()
                ->title('Terlalu banyak percobaan')
                ->body('Silakan coba lagi dalam beberapa saat.')
                ->danger()
                ->send();
                
            return null;
        }

        $data = $this->form->getState();

        // Ambil role divisi dan hapus dari data
        $divisiRole = $data['divisi_role'];
        unset($data['divisi_role']);

        $data['password'] = Hash::make($data['password']);
        // Set admin_verified ke false secara default
        $data['admin_verified'] = false;

        $user = User::create($data);

        // Assign role divisi yang dipilih
        $user->assignRole($divisiRole);

        // Tetap memicu event Registered untuk listener lain
        event(new Registered($user));

        // Tampilkan pesan ke pengguna
        Notification::make()
            ->title('Pendaftaran Berhasil')
            ->body('Mohon menunggu akun anda diverifikasi oleh admin, status verifikasi akan dikirim via email')
            ->success()
            ->send();

        // Redirect ke halaman login
        return app(RegistrationResponse::class);
    }

    protected function redirectPath(string $redirectRoute): RegistrationResponse
    {
        return app(RegistrationResponse::class);
    }
}