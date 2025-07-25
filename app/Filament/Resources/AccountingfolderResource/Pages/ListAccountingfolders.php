<?php

namespace App\Filament\Resources\AccountingfolderResource\Pages;

use App\Filament\Resources\AccountingfolderResource;
use App\Models\Accountingfolder;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAccountingfolders extends ListRecords
{
    protected static string $resource = AccountingfolderResource::class;

   protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Folder')
                ->icon('heroicon-o-folder-plus')
                ->modalHeading('Buat Folder Baru')
                ->modalSubmitActionLabel('Buat Folder')
                ->modalCancelActionLabel('Batal')
                ->mutateFormDataUsing(function (array $data): array {
                    // Pastikan user_id diset ke user yang sedang login
                    $data['user_id'] = filament()->auth()->id();
                    return $data;
                }),
        ];
    }

    public function mount(): void
    {
        parent::mount();
        session()->forget('folder_id');
        session()->forget('folder_password');
    }

    public function folderAction(?Accountingfolder $item = null)
    {
        return Actions\Action::make('folderAction')
            ->requiresConfirmation(function (array $arguments) {
                return $arguments['record']['is_protected'] ?? false;
            })
            ->modalHeading(function (array $arguments) {
                if ($arguments['record']['is_protected']) {
                    return 'Masukkan Password untuk "' . $arguments['record']['name'] . '"';
                }
                return '';
            })
            ->form(function (array $arguments) {
                if ($arguments['record']['is_protected']) {
                    return [
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255),
                    ];
                }
                return [];
            })
            ->action(function (array $arguments, array $data) {
                $record = $arguments['record'];
                
                // Cek password jika folder terproteksi
                if ($record['is_protected']) {
                    if ($record['password'] != $data['password']) {
                        Notification::make()
                            ->title('Password salah')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Simpan session password
                    session()->put('folder_password_' . $record['id'], $data['password']);
                }

                // Redirect ke media menggunakan slug, bukan ID
                $folder = Accountingfolder::find($record['id']);
                if (!$folder) {
                    Notification::make()
                        ->title('Folder tidak ditemukan')
                        ->danger()
                        ->send();
                    return;
                }

                // Redirect berdasarkan jenis folder
                if (!$record['model_type']) {
                    // Folder standalone, redirect dengan slug
                    return redirect()->route('filament.admin.resources.arsip.akuntansi.folder.index', [
                        'folder' => $folder->slug // Gunakan slug, bukan folder_id
                    ]);
                }

                // Logic untuk folder dengan model_type (jika ada)
                if (!$record['model_id'] && !$record['collection']) {
                    return redirect()->route('filament.admin.resources.arsip.akuntansi.index', [
                        'model_type' => $record['model_type']
                    ]);
                } elseif (!$record['model_id']) {
                    return redirect()->route('filament.admin.resources.arsip.akuntansi.index', [
                        'model_type' => $record['model_type'],
                        'collection' => $record['collection']
                    ]);
                } else {
                    return redirect()->route('filament.admin.resources.arsip.akuntansi.folder.index', [
                        'folder' => $folder->slug // Gunakan slug, bukan folder_id
                    ]);
                }
            })
            ->view('folders.akuntansi.folderaction', ['item' => $item]);
    }
}
