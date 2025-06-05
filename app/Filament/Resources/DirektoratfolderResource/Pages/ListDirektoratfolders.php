<?php

namespace App\Filament\Resources\DirektoratfolderResource\Pages;

use App\Filament\Resources\DirektoratfolderResource;
use App\Models\Direktoratfolder;
use Filament\Actions;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDirektoratfolders extends ListRecords
{
    protected static string $resource = DirektoratfolderResource::class;
    
   protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Folder')
                ->icon('heroicon-o-folder-plus'),
        ];
    }

    public function mount(): void
    {
        session()->forget('folder_id');
        session()->forget('folder_password');
    }

    public function folderAction(?Direktoratfolder $item = null)
    {
        return Actions\Action::make('folderAction')
            ->requiresConfirmation(function (array $arguments) {
                if($arguments['record']['is_protected']) {
                    return true;
                }
                else {
                    return false;
                }
            })
            ->modalHeading(function (array $arguments) {
                if($arguments['record']['is_protected']) {
                    return 'Masukkan Password untuk "' . $arguments['record']['name'] . '"';
                }
                return '';
            })
            ->form(function (array $arguments) {
                if($arguments['record']['is_protected']) {
                    return [
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255),
                    ];
                }
                else {
                    return null;
                }
            })
            ->action(function (array $arguments, array $data) {
                if($arguments['record']['is_protected']) {
                    if($arguments['record']['password'] != $data['password']) {
                        Notification::make()
                            ->title('Password salah')
                            ->danger()
                            ->send();

                        return;
                    }
                    else {
                        session()->put('folder_password', $data['password']);
                    }
                }

                // Redirect ke media berdasarkan jenis folder
                if(!$arguments['record']['model_type']) {
                    return redirect()->route('filament.admin.resources.direktoratmedia.index', [
                        'folder_id' => $arguments['record']['id']
                    ]);
                }

                if(!$arguments['record']['model_id'] && !$arguments['record']['collection']) {
                    return redirect()->route('filament.admin.resources.direktoratfolders.index', [
                        'model_type' => $arguments['record']['model_type']
                    ]);
                }
                else if(!$arguments['record']['model_id']) {
                    return redirect()->route('filament.admin.resources.direktoratfolders.index', [
                        'model_type' => $arguments['record']['model_type'], 
                        'collection' => $arguments['record']['collection']
                    ]);
                }
                else {
                    return redirect()->route('filament.admin.resources.direktoratmedia.index', [
                        'folder_id' => $arguments['record']['id']
                    ]);
                }
            })
            ->view('folders.folderaction', ['item' => $item]);
    }
    
}
