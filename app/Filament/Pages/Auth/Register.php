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
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as FilamentRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Support\Enums\Alignment;
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
                Section::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                Section::make('Informasi Pribadi')
                                    ->description('Isi informasi pribadi Anda untuk membuat akun.')
                                    ->icon('heroicon-o-user-circle')
                                    ->collapsible(false)
                                    ->compact()
                                    
                                    ->columns(2)
                                    ->schema([
                                        $this->getNameFormComponent(),
                                        $this->getEmailFormComponent(),
                                    ]),

                                Section::make('Informasi Divisi')
                                    ->description('Pilih divisi tempat Anda bekerja.')
                                    ->icon('heroicon-o-building-office-2')
                                    ->collapsible(false)
                                    ->compact()
                                    ->schema([
                                        $this->getDivisiFormComponent(),
                                    ]),
                                
                                Section::make('Keamanan')
                                    ->description('Buat kata sandi yang kuat untuk akun Anda.')
                                    ->icon('heroicon-o-shield-check')
                                    ->collapsible(false)
                                    ->compact()
                                    ->columns(2)
                                    ->schema([
                                        $this->getPasswordFormComponent(),
                                        $this->getPasswordConfirmationFormComponent(),
                                    ]),
                            ])
                            ->columns(1),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    // Metode untuk komponen form divisi dengan UI yang lebih baik
    protected function getDivisiFormComponent(): Component
    {
        // Definisikan opsi divisi secara hardcoded
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
            ->label('Divisi')
            ->placeholder('Pilih Divisi Anda')
            ->searchable()
            ->preload()
            ->options($divisiOptions)
            ->required()
            ->native(false)
            ->helperText('Pilih divisi sesuai dengan posisi Anda saat ini')
            ->reactive()
            ->prefixIcon('heroicon-o-building-office-2')
            ->extraAttributes(['class' => 'max-w-md mx-auto'])
            ->columnSpanFull();
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Nama Pengguna')
            ->placeholder('Masukkan nama lengkap Anda')
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->autocomplete('name')
            ->prefixIcon('heroicon-o-user')
            ->extraAttributes(['class' => 'max-w-md']);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->email()
            ->placeholder('contoh@perusahaan.com')
            ->required()
            ->maxLength(255)
            ->unique(table: User::class, column: 'email')
            ->autocomplete('email')
            ->prefixIcon('heroicon-o-envelope')
            ->extraAttributes(['class' => 'max-w-md']);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi')
            ->password()
            ->placeholder('Minimal 8 karakter')
            ->required()
            ->minLength(8)
            ->confirmed()
            ->autocomplete('new-password')
            ->helperText('Kata sandi harus terdiri dari minimal 8 karakter')
            ->prefixIcon('heroicon-o-lock-closed')
            ->revealable()
            ->extraAttributes(['class' => 'max-w-md']);
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('password_confirmation')
            ->label('Konfirmasi Kata Sandi')
            ->password()
            ->placeholder('Masukkan ulang kata sandi')
            ->required()
            ->minLength(8)
            ->dehydrated(false)
            ->autocomplete('new-password')
            ->prefixIcon('heroicon-o-shield-check')
            ->revealable()
            ->extraAttributes(['class' => 'max-w-md']);
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
            ->title('Pendaftaran Berhasil!')
            ->body('Terima kasih telah mendaftar. Mohon menunggu akun Anda diverifikasi oleh admin. Kami akan mengirimkan notifikasi status via email.')
            ->success()
            ->persistent()
            ->send();

        // Redirect ke halaman login
        return app(RegistrationResponse::class);
    }

    protected function redirectPath(string $redirectRoute): RegistrationResponse
    {
        return app(RegistrationResponse::class);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();
        
        // Modifikasi tombol submit untuk tampilan yang lebih menarik
        if (isset($actions[0])) {
            $actions[0]->label('Buat Akun Sekarang')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->size('lg')
                ->extraAttributes([
                    'class' => 'w-full md:w-auto',
                ]);
        }
        
        return $actions;
    }
}