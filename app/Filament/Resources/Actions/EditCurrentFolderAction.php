<?php

namespace App\Filament\Resources\Actions;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use TomatoPHP\FilamentIcons\Components\IconPicker;

class EditCurrentFolderAction
{
    public static function make(int $folder_id): Actions\Action
    {
        $form = config('filament-media-manager.model.folder')::query()->where('id',$folder_id)->with('users')->first()?->toArray();
        $form['users'] = collect($form['users'])->pluck('id')->toArray();

        return Actions\Action::make('edit_current_folder')
            ->hiddenLabel()
            ->mountUsing(function () use ($folder_id){
                session()->put('folder_id', $folder_id);
            })
            ->tooltip('Ubah Folder')
            ->label('Edit Folder')
            ->icon('heroicon-o-pencil-square')
            ->color('warning')
            ->form(function (){
                return [
                    Grid::make([
                        "sm" => 1,
                        "md" => 2
                    ])
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nama Folder')
                                ->columnSpanFull()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('description')
                                ->label('Deskripsi')
                                ->columnSpanFull()
                                ->maxLength(255),
                            IconPicker::make('icon')
                                ->label('Ikon'),
                            Forms\Components\ColorPicker::make('color')
                                ->label('Warna'),
                            Forms\Components\Toggle::make('is_protected')
                                ->label('Proteksi')
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('password')
                                ->label('Password')
                                ->hidden(fn(Forms\Get $get) => !$get('is_protected'))
                                ->confirmed()
                                ->password()
                                ->revealable()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('password_confirmation')
                                ->label('Konfirmasi Password')
                                ->hidden(fn(Forms\Get $get) => !$get('is_protected'))
                                ->password()
                                ->required()
                                ->revealable()
                                ->maxLength(255),
                            Forms\Components\Toggle::make('is_public')
                                ->visible(filament('filament-media-manager')->allowUserAccess)
                                ->label('Publik')
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('has_user_access')
                                ->visible(filament('filament-media-manager')->allowUserAccess)
                                ->hidden(fn(Forms\Get $get) => $get('is_public'))
                                ->label('Akses Pengguna')
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\Select::make('users')
                                ->required()
                                ->visible(filament('filament-media-manager')->allowUserAccess)
                                ->hidden(fn(Forms\Get $get) => !$get('has_user_access'))
                                ->label('Pengguna')
                                ->searchable()
                                ->multiple()
                                ->options(User::query()->where('id', '!=', filament()->auth()->user()->id)->pluck(config('filament-media-manager.user.column_name'), 'id')->toArray())
                        ])
                ];
            })
            ->fillForm($form)
            ->action(function (array $data) use ($folder_id){
                $folder = config('filament-media-manager.model.folder')::find($folder_id);
                $folder->update($data);

                if(isset($data['users'])){
                    $folder->users()->sync($data['users']);
                }

                Notification::make()
                ->title('Folder berhasil diperbarui')
                ->success()
                ->send();
            });
    }
}
