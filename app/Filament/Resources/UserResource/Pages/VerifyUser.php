<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class VerifyUser extends Page
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.verify-user';

    public User $record;
    
    public ?array $data = [];

    public function mount(User $record): void
    {
        $this->record = $record;
        $this->form->fill([
            'name' => $record->name,
            'email' => $record->email,
            'admin_verified' => $record->admin_verified,
            'is_admin' => $record->is_admin,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pengguna')
                    ->description('Lihat dan verifikasi pengguna ini')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->disabled(),
                        TextInput::make('email')
                            ->label('Email')
                            ->disabled(),
                        Toggle::make('admin_verified')
                            ->label('Diverifikasi Admin')
                            ->helperText('Aktifkan untuk mengizinkan pengguna login ke sistem'),
                        Toggle::make('is_admin')
                            ->label('Admin')
                            ->helperText('Aktifkan untuk memberikan akses admin ke pengguna'),
                    ])->columns(2)
            ])
            ->statePath('data');
    }

    public function verifyUser(): void
    {
        $data = $this->form->getState();
        
        $this->record->admin_verified = $data['admin_verified'];
        $this->record->is_admin = $data['is_admin'];
        $this->record->save();
        
        // Notifikasi untuk pengguna
        if ($data['admin_verified']) {
            // Notify the user that their account has been verified
            $this->notifyUserVerified($this->record);
        }
        
        Notification::make()
            ->title('Status pengguna berhasil diperbarui')
            ->success()
            ->send();
            
        $this->redirect(UserResource::getUrl('index'));
    }

    public function rejectUser(): void
    {
        // Opsi untuk menolak dan menghapus pengguna jika diperlukan
        $this->record->delete();
        
        Notification::make()
            ->title('Pengguna ditolak dan dihapus')
            ->warning()
            ->send();
            
        $this->redirect(UserResource::getUrl('index'));
    }
    
    /**
     * Mengirim notifikasi ke pengguna bahwa akun mereka telah diverifikasi
     */
    protected function notifyUserVerified(User $user): void
    {
        // Implementasi notifikasi email ke pengguna
        // Di sini bisa dibuat notifikasi baru atau menggunakan Job
        
        // Untuk saat ini, kita hanya mencatat di log
        \Illuminate\Support\Facades\Log::info("User {$user->name} has been verified by admin");
    }
}