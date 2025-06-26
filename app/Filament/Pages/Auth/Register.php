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

                                Section::make('Informasi Posisi & Divisi')
                                    ->description('Pilih tingkat posisi dan divisi tempat Anda bekerja.')
                                    ->icon('heroicon-o-building-office-2')
                                    ->collapsible(false)
                                    ->compact()
                                    ->columns(2)
                                    ->schema([
                                        $this->getJenjangFormComponent(),
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
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'max-w-4xl mx-auto w-full',
                        'style' => 'width: 100%; max-width: 56rem;'
                    ]),
            ])
            ->statePath('data')
            ->extraAttributes([
                'class' => 'w-full max-w-none',
                'style' => 'max-width: none !important; width: 100% !important;'
            ]);
    }

    // Komponen untuk memilih jenjang posisi
    protected function getJenjangFormComponent(): Component
    {
        return Select::make('jenjang_posisi')
            ->label('Jenjang Posisi')
            ->placeholder('Pilih Jenjang Posisi')
            ->options([
                'staff' => 'Staff/Karyawan',
                'kepala_divisi' => 'Kepala Divisi',
                'direktur' => 'Direktur',
            ])
            ->required()
            ->native(false)
            ->helperText('Pilih jenjang posisi sesuai dengan jabatan Anda')
            ->reactive()
            ->live()
            ->prefixIcon('heroicon-o-identification')
            ->afterStateUpdated(function (callable $set, $state) {
                // Reset divisi selection when jenjang changes
                $set('divisi_role', null);
            })
            ->extraAttributes(['class' => 'w-full']);
    }

    // Komponen untuk divisi dengan logika berdasarkan jenjang
    protected function getDivisiFormComponent(): Component
    {
        return Select::make('divisi_role')
            ->label('Divisi')
            ->placeholder(function (callable $get) {
                $jenjang = $get('jenjang_posisi');
                if (!$jenjang) {
                    return 'Pilih jenjang posisi terlebih dahulu';
                }
                return 'Pilih Divisi Anda';
            })
            ->searchable()
            ->preload()
            ->options(function (callable $get) {
                $jenjang = $get('jenjang_posisi');

                if (!$jenjang) {
                    return [];
                }

                return $this->getDivisiOptionsByJenjang($jenjang);
            })
            ->required()
            ->native(false)
            ->helperText(function (callable $get) {
                $jenjang = $get('jenjang_posisi');

                switch ($jenjang) {
                    case 'staff':
                        return 'Pilih divisi tempat Anda bekerja sebagai staff/karyawan';
                    case 'kepala_divisi':
                        return 'Pilih divisi yang akan Anda pimpin';
                    case 'direktur':
                        return 'Pilih bidang direktur yang sesuai';
                    default:
                        return 'Pilih divisi sesuai dengan posisi Anda';
                }
            })
            ->reactive()
            ->live()
            ->prefixIcon('heroicon-o-building-office-2')
            ->disabled(fn(callable $get) => !$get('jenjang_posisi'))
            ->extraAttributes(['class' => 'w-full'])
            ->columnSpanFull();
    }

    // Method untuk mendapatkan opsi divisi berdasarkan jenjang
    protected function getDivisiOptionsByJenjang(string $jenjang): array
    {
        $baseDivisions = [
            'hrd' => 'HRD & GA',
            'keuangan' => 'Keuangan',
            'software' => 'Software',
            'purchasing' => 'Purchasing',
            'elektro' => 'Elektro',
            'r&d' => 'R&D',
            '3d' => '3D',
            'mekanik' => 'Mekanik',
            'pmo' => 'PMO',
        ];

        switch ($jenjang) {
            case 'staff':
                $options = [];
                foreach ($baseDivisions as $key => $name) {
                    $options["divisi_{$key}"] = $name;
                }
                // Tambahkan Manager HRD sebagai opsi khusus untuk staff
                $options['divisi_manager_hrd'] = 'Manager HRD';
                return $options;

            case 'kepala_divisi':
                $options = [];
                foreach ($baseDivisions as $key => $name) {
                    $options["kepala_divisi_{$key}"] = "Kepala Divisi {$name}";
                }
                return $options;

            case 'direktur':
                return [
                    'direktur_utama' => 'Direktur Utama',
                    'direktur_teknologi' => 'Direktur Teknologi',
                    'direktur_produk' => 'Direktur Produk',
                    'direktur_project' => 'Direktur Project',
                    'direktur_keuangan' => 'Direktur Keuangan',
                ];

            default:
                return [];
        }
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Nama Pengguna')
            ->placeholder('Masukkan nama pengguna')
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->autocomplete('name')
            ->prefixIcon('heroicon-o-user')
            ->extraAttributes(['class' => 'w-full']);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->email()
            ->placeholder('contoh@avsimulator.com')
            ->required()
            ->maxLength(255)
            ->unique(table: User::class, column: 'email')
            ->autocomplete('email')
            ->prefixIcon('heroicon-o-envelope')
            ->extraAttributes(['class' => 'w-full']);
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
            ->extraAttributes(['class' => 'w-full']);
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
            ->extraAttributes(['class' => 'w-full']);
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

        // Ambil role divisi dan jenjang, kemudian hapus dari data
        $divisiRole = $data['divisi_role'];
        $jenjangPosisi = $data['jenjang_posisi'];

        unset($data['divisi_role'], $data['jenjang_posisi']);

        $data['password'] = Hash::make($data['password']);
        // Set admin_verified ke false secara default
        $data['admin_verified'] = false;

        // Tentukan jabatan dan divisi berdasarkan jenjang dan divisi role
        $data['jabatan'] = $this->determineJabatan($jenjangPosisi, $divisiRole);
        $data['jenjang_posisi'] = $jenjangPosisi;
        $data['divisi'] = $this->extractDivisiName($divisiRole);

        $user = User::create($data);

        // Assign role divisi yang dipilih
        $user->assignRole($divisiRole);

        // Tetap memicu event Registered untuk listener lain
        event(new Registered($user));

        // Tampilkan pesan ke pengguna berdasarkan jenjang
        $this->showRegistrationNotification($jenjangPosisi);

        // Redirect ke halaman login
        return app(RegistrationResponse::class);
    }

    // Method untuk menentukan jabatan berdasarkan jenjang dan divisi
    protected function determineJabatan(string $jenjang, string $divisiRole): string
    {
        // Extract divisi name from role
        $divisiName = $this->extractDivisiName($divisiRole);

        switch ($jenjang) {
            case 'staff':
                return "Staff {$divisiName}";
            case 'kepala_divisi':
                return "Kepala Divisi {$divisiName}";
            case 'direktur':
                // For direktur, use the full role name
                return ucwords(str_replace('_', ' ', $divisiRole));
            default:
                return $divisiName;
        }
    }

    // Method untuk mengekstrak nama divisi dari role
    protected function extractDivisiName(string $role): string
    {
        $roleMap = [
            // Staff roles
            'divisi_manager_hrd' => 'Manager HRD',
            'divisi_hrd' => 'HRD & GA',
            'divisi_keuangan' => 'Keuangan',
            'divisi_software' => 'Software',
            'divisi_purchasing' => 'Purchasing',
            'divisi_elektro' => 'Elektro',
            'divisi_r&d' => 'R&D',
            'divisi_3d' => '3D',
            'divisi_mekanik' => 'Mekanik',
            'divisi_pmo' => 'PMO',

            // Kepala divisi roles
            'kepala_divisi_hrd' => 'HRD & GA',
            'kepala_divisi_keuangan' => 'Keuangan',
            'kepala_divisi_software' => 'Software',
            'kepala_divisi_purchasing' => 'Purchasing',
            'kepala_divisi_elektro' => 'Elektro',
            'kepala_divisi_r&d' => 'R&D',
            'kepala_divisi_3d' => '3D',
            'kepala_divisi_mekanik' => 'Mekanik',
            'kepala_divisi_pmo' => 'PMO',

            // Direktur roles
            'direktur_utama' => 'Direktur Utama',
            'direktur_teknologi' => 'Direktur Teknologi',
            'direktur_produk' => 'Direktur Produk',
            'direktur_project' => 'Direktur Project',
            'direktur_keuangan' => 'Direktur Keuangan',
        ];

        return $roleMap[$role] ?? ucwords(str_replace(['_', 'divisi', 'kepala', 'direktur'], [' ', '', '', ''], $role));
    }

    // Method untuk menampilkan notifikasi berdasarkan jenjang
    protected function showRegistrationNotification(string $jenjang): void
    {
        $messages = [
            'staff' => [
                'title' => 'Pendaftaran Staff Berhasil!',
                'body' => 'Terima kasih telah mendaftar sebagai staff. Mohon menunggu akun Anda diverifikasi oleh admin. Kami akan mengirimkan notifikasi status via email.'
            ],
            'kepala_divisi' => [
                'title' => 'Pendaftaran Kepala Divisi Berhasil!',
                'body' => 'Terima kasih telah mendaftar sebagai Kepala Divisi. Akun Anda memerlukan verifikasi khusus dari manajemen. Kami akan segera meninjau dan mengirimkan notifikasi via email.'
            ],
            'direktur' => [
                'title' => 'Pendaftaran Direktur Berhasil!',
                'body' => 'Terima kasih telah mendaftar sebagai Direktur. Akun Anda memerlukan verifikasi tingkat tinggi dari super admin. Tim kami akan segera meninjau pendaftaran Anda.'
            ]
        ];

        $message = $messages[$jenjang] ?? $messages['staff'];

        Notification::make()
            ->title($message['title'])
            ->body($message['body'])
            ->success()
            ->persistent()
            ->send();
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

    // Tambahkan method untuk mengatur lebar form container
    public function getMaxWidth(): string
    {
        return 'xl'; // Bisa juga '5xl', '6xl', atau '7xl' untuk lebih besar
    }
}
