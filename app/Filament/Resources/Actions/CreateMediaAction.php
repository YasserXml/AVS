<?php
namespace App\Filament\Resources\Actions;

use App\Models\Direktoratfolder;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class CreateMediaAction
{
    public static function make(int $folder_id): Action
    {
        return Action::make('create_media')
            ->mountUsing(function () use ($folder_id){
                session()->put('folder_id', $folder_id);
            })
            ->label('Tambah Media')
            ->icon('heroicon-o-plus')
            ->form([
                FileUpload::make('file')
                    ->label('File')
                    ->maxSize('100000')
                    ->columnSpanFull()
                    ->required()
                    ->storeFiles(false),
                TextInput::make('title')
                    ->label('Judul')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data) use ($folder_id) {
                $folder = Direktoratfolder::find($folder_id);
                if($folder){
                    if($folder->model){
                        $folder->model->addMedia($data['file'])
                            ->withCustomProperties([
                                'title' => $data['title'],
                                'description' => $data['description']
                            ])
                            ->toMediaCollection($folder->collection);
                    }
                    else {
                        $folder->addMedia($data['file'])
                            ->withCustomProperties([
                                'title' => $data['title'],
                                'description' => $data['description']
                            ])
                            ->toMediaCollection($folder->collection);
                    }

                }

                Notification::make()
                ->title('Media berhasil dibuat')
                ->success()
                ->send();
            });
    }
}
