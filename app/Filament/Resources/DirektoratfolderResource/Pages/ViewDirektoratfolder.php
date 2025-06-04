<?php
namespace App\Filament\Resources\DirektoratFolderResource\Pages;

use App\Filament\Resources\DirektoratFolderResource;
use App\Filament\Resources\DirektoratFolderResource\Widgets\FolderContentWidget;
use App\Models\DirektoratFolder;
use App\Models\DirektoratMedia;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Illuminate\Support\Str;

class ViewDirektoratFolder extends ViewRecord
{
    protected static string $resource = DirektoratFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add_media')
                ->label('Tambah Media')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->form([
                    FileUpload::make('files')
                        ->label('File Media')
                        ->multiple()
                        ->acceptedFileTypes(['image/*', 'video/*', 'audio/*', 'application/pdf'])
                        ->maxSize(10240)
                        ->directory('media')
                        ->required(),
                        
                    TextInput::make('collection_name')
                        ->label('Nama Koleksi')
                        ->default('default')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    foreach ($data['files'] as $file) {
                        $this->record->media()->create([
                            'model_type' => DirektoratFolder::class,
                            'model_id' => $this->record->id,
                            'uuid' => Str::uuid(),
                            'collection_name' => $data['collection_name'],
                            'name' => pathinfo($file, PATHINFO_FILENAME),
                            'file_name' => basename($file),
                            'mime_type' => mime_content_type(storage_path('app/public/' . $file)),
                            'disk' => 'public',
                            'size' => filesize(storage_path('app/public/' . $file)),
                            'manipulations' => [],
                            'custom_properties' => [],
                            'generated_conversions' => [],
                            'responsive_images' => [],
                            'order_column' => 1,
                        ]);
                    }
                }),
                
            Actions\Action::make('create_subfolder')
                ->label('Buat Subfolder')
                ->icon('heroicon-m-folder-plus')
                ->color('success')
                ->form([
                    TextInput::make('name')
                        ->label('Nama Subfolder')
                        ->required()
                        ->maxLength(255),
                        
                    TextInput::make('description')
                        ->label('Deskripsi')
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    DirektoratFolder::create([
                        'model_type' => DirektoratFolder::class,
                        'model_id' => $this->record->id,
                        'name' => $data['name'],
                        'description' => $data['description'] ?? null,
                        'user_id' => filament()->auth()->id(),
                        'collection' => $this->record->collection ?? 'default',
                        'icon' => 'heroicon-o-folder',
                        'color' => '#10b981',
                    ]);
                }),
                
            Actions\EditAction::make()
                ->label('Edit Folder'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Folder')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Folder'),
                            
                        TextEntry::make('description')
                            ->label('Deskripsi'),
                            
                        TextEntry::make('user.name')
                            ->label('Dibuat oleh'),
                            
                        TextEntry::make('created_at')
                            ->label('Dibuat pada')
                            ->dateTime(),
                    ])
                    ->columns(2),
                    
                Section::make('Pengaturan')
                    ->schema([
                        IconEntry::make('is_public')
                            ->label('Akses Publik')
                            ->boolean(),
                            
                        IconEntry::make('is_favorite')
                            ->label('Favorit')
                            ->boolean(),
                            
                        IconEntry::make('is_protected')
                            ->label('Terlindungi')
                            ->boolean(),
                            
                        IconEntry::make('is_hidden')
                            ->label('Tersembunyi')
                            ->boolean(),
                    ])
                    ->columns(2),
            ]);
    }
    
    public function getContentTabLabel(): ?string
    {
        return 'Konten';
    }
}
