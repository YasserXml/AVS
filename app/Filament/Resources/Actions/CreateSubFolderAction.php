<?php

namespace App\Filament\Resources\Actions;

use App\Models\Direktoratfolder;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use illuminate\Support\Str;
use TomatoPHP\FilamentIcons\Components\IconPicker;

class CreateSubFolderAction
{
    public static function make(int $folder_id): Action
    {
        return Action::make('create_sub_folder')
            ->hidden(fn()=> !filament('filament-media-manager')->allowSubFolders)
            ->mountUsing(function () use ($folder_id){
                session()->put('folder_id', $folder_id);
            })
            ->color('info')
            ->hiddenLabel()
            ->tooltip('Buat Sub Folder')
            ->label('Sub folder')
            ->icon('heroicon-o-folder-minus')
            ->form([
                TextInput::make('name')
                    ->label('Nama')
                    ->columnSpanFull()
                    ->lazy()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $set('collection', Str::slug($get('name')));
                    })
                    ->required()
                    ->maxLength(255),
                TextInput::make('collection')
                    ->label('Koleksi')
                    ->columnSpanFull()
                    ->unique(Direktoratfolder::class)
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull()
                    ->maxLength(255),
                IconPicker::make('icon')
                    ->label('Ikon'),
                ColorPicker::make('color')
                    ->label('Warna'),
                Toggle::make('is_protected')
                    ->label('Lindungi Folder')
                    ->live()
                    ->columnSpanFull(),
                TextInput::make('password')
                    ->label('Password')
                    ->hidden(fn(Get $get) => !$get('is_protected'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->hidden(fn(Get $get) => !$get('is_protected'))
                    ->password()
                    ->required()
                    ->revealable()
                    ->maxLength(255)
            ])
            ->action(function (array $data) use ($folder_id) {
                $folder = Direktoratfolder::find($folder_id);
                if($folder){
                    $data['user_id'] = filament()->auth()->user()->id;
                    $data['user_type'] = get_class(filament()->auth()->user());
                    $data['model_id'] = $folder_id;
                    $data['model_type'] = Direktoratfolder::class;
                    Direktoratfolder::query()->create($data);
                }

                Notification::make()
                    ->title('Folder Created')
                    ->body('Folder Created Successfully')
                    ->success()
                    ->send();
            });
    }
}