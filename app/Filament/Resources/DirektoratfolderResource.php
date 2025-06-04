<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DirektoratfolderResource\Pages;
use App\Filament\Resources\DirektoratfolderResource\RelationManagers;
use App\Models\Direktoratfolder;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DirektoratfolderResource extends Resource
{
    protected static ?string $model = Direktoratfolder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Folder')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(filament()->auth()->id()),
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
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('is_protected')
                            ->label('Folder Terlindungi')
                            ->default(false)
                            ->live(),
                            
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->visible(fn (Forms\Get $get) => $get('is_protected')),
                            
                        Toggle::make('is_hidden')
                            ->label('Folder Tersembunyi')
                            ->default(false),
                            
                        Toggle::make('is_favorite')
                            ->label('Favorit')
                            ->default(false),
                            
                        Toggle::make('is_public')
                            ->label('Akses Publik')
                            ->default(false),
                            
                        Toggle::make('has_user_access')
                            ->label('Akses Pengguna Khusus')
                            ->default(false)
                            ->live(),
                            
                        Select::make('user_type')
                            ->label('Tipe Pengguna')
                            ->options([
                                'admin' => 'Administrator',
                                'editor' => 'Editor',
                                'viewer' => 'Viewer',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('has_user_access')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Stack::make([
                        TextColumn::make('name')
                            ->label('Nama')
                            ->weight(FontWeight::Bold)
                            ->size('lg')
                            ->searchable()
                            ->sortable(),
                            
                        TextColumn::make('media_count')
                            ->label('')
                            ->getStateUsing(function (DirektoratFolder $record) {
                                $mediaCount = $record->media()->count();
                                $folderCount = $record->subfolders()->count();
                                
                                $text = [];
                                if ($mediaCount > 0) {
                                    $text[] = "{$mediaCount} media yang lalu";
                                }
                                if ($folderCount > 0) {
                                    $text[] = "{$folderCount} subfolder";
                                }
                                
                                return empty($text) ? '0 item' : implode(' • ', $text);
                            })
                            ->color('gray')
                            ->size('sm'),
                    ])->space(1),
                ])
                ->space(2),
            ])
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->filters([
                SelectFilter::make('is_favorite')
                    ->label('Favorit')
                    ->options([
                        '1' => 'Ya',
                        '0' => 'Tidak',
                    ]),
                    
                SelectFilter::make('is_public')
                    ->label('Akses Publik')
                    ->options([
                        '1' => 'Ya',
                        '0' => 'Tidak',
                    ]),
            ])
            ->actions([
                // Action::make('view')
                //     ->label('Buka')
                //     ->icon('heroicon-m-eye')
                //     ->url(fn (DirektoratFolder $record): string => route('filament.admin.resources.direktorate-folders.view', $record))
                //     ->color('primary'),
                    
                Action::make('create_subfolder')
                    ->label('Buat Subfolder')
                    ->icon('heroicon-m-folder-plus')
                    ->form([
                        TextInput::make('name')
                            ->label('Nama Subfolder')
                            ->required()
                            ->maxLength(255),
                            
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),
                    ])
                    ->action(function (array $data, DirektoratFolder $record): void {
                        $record->subfolders()->create([
                            'name' => $data['name'],
                            'description' => $data['description'] ?? null,
                            'user_id' => filament()->auth()->id(),
                            'collection' => $record->collection,
                        ]);
                    })
                    ->color('success'),
                    
                Action::make('add_media')
                    ->label('Tambah Media')
                    ->icon('heroicon-m-plus')
                    ->form([
                        Forms\Components\FileUpload::make('files')
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
                    ->action(function (array $data, DirektoratFolder $record): void {
                        foreach ($data['files'] as $file) {
                            $record->media()->create([
                                'uuid' => Str::uuid(),
                                'collection_name' => $data['collection_name'],
                                'name' => pathinfo($file, PATHINFO_FILENAME),
                                'file_name' => basename($file),
                                'mime_type' => mime_content_type(storage_path('app/public/' . $file)),
                                'disk' => 'public',
                                'size' => filesize(storage_path('app/public/' . $file)),
                                'manipulations' => json_encode([]),
                                'custom_properties' => json_encode([]),
                                'generated_conversions' => json_encode([]),
                                'responsive_images' => json_encode([]),
                                'order_column' => 1,
                            ]);
                        }
                    })
                    ->color('warning'),
                    
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->color('gray'),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Folder')
                    ->icon('heroicon-m-folder-plus'),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDirektoratfolders::route('/'),
            // 'create' => Pages\CreateDirektoratfolder::route('/create'),
            'view' => Pages\ViewDirektoratfolder::route('/{record}'),
            // 'edit' => Pages\EditDirektoratfolder::route('/{record}/edit'),
        ];
    }
}
