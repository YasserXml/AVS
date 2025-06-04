<?php

namespace App\Filament\Resources\DirektoratfolderResource\Pages;

use App\Filament\Resources\DirektoratfolderResource;
use Filament\Actions;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ListRecords;

class ListDirektoratfolders extends ListRecords
{
    protected static string $resource = DirektoratfolderResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Folder')
                ->icon('heroicon-m-folder-plus')
                ->color('primary')
                ->form([
                    TextInput::make('name')
                        ->label('Nama Folder')
                        ->required()
                        ->maxLength(255),
                        
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->maxLength(500),
                        
                    Select::make('icon')
                        ->label('Icon')
                        ->options([
                            'heroicon-o-folder' => 'Folder',
                            'heroicon-o-document' => 'Document',
                            'heroicon-o-photo' => 'Photo',
                            'heroicon-o-video-camera' => 'Video',
                            'heroicon-o-musical-note' => 'Audio',
                        ])
                        ->default('heroicon-o-folder'),
                        
                    ColorPicker::make('color')
                        ->label('Warna')
                        ->default('#10b981'),
                        
                    Toggle::make('is_public')
                        ->label('Akses Publik')
                        ->default(false),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = filament()->auth()->id();
                    $data['collection'] = 'default';
                    return $data;
                })
                ->modalWidth('lg'),
        ];
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()
            ->whereNull('model_type')
            ->whereNull('model_id')
            ->with(['media', 'subfolders', 'user']);
    }
}
