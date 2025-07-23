<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MekanikmediaResource\Pages;
use App\Filament\Resources\MekanikmediaResource\Pages\ListMekanikmedia;
use App\Filament\Resources\MekanikmediaResource\RelationManagers;
use App\Models\Mekanikfolder;
use App\Models\Mekanikmedia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MekanikmediaResource extends Resource
{
    protected static ?string $model = Mekanikmedia::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function setBreadcrumb(?string $breadcrumb): void
    {
        self::$breadcrumb = $breadcrumb;
    }

    public static function getSlug(): string
    {
        return 'arsip/mekanik/folder';
    }

    public static function getNavigationSort(): ?int
    {
        return 32;
    }

    public static function getUrlFromFolderMekanik(Mekanikfolder $folder, string $name = 'index'): string
    {
        return static::getUrl($name, ['folder' => $folder->slug]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
             ->modifyQueryUsing(function (Builder $query) {
                // Ambil folder berdasarkan slug
                if (request()->has('folder')) {
                    $folderSlug = request()->get('folder');
                    $folder = Mekanikfolder::where('slug', $folderSlug)->first();

                    if ($folder && $folder->canBeAccessedBy()) {
                        $query->where('model_type', Mekanikfolder::class)
                            ->where('model_id', $folder->id)
                            ->where('user_id', filament()->auth()->id()); // Pastikan hanya media milik user
                    } else {
                        // Jika folder tidak ditemukan atau tidak dapat diakses, kosongkan query
                        $query->whereRaw('1 = 0');
                    }
                }
                // Fallback untuk folder_id (backward compatibility)
                elseif (request()->has('folder_id')) {
                    $folderId = request()->get('folder_id');
                    $folder = Mekanikfolder::find($folderId);

                    if ($folder && $folder->canBeAccessedBy()) {
                        $query->where('model_type', Mekanikfolder::class)
                            ->where('model_id', $folderId)
                            ->where('user_id', filament()->auth()->id());
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                } else {
                    // Tidak ada parameter folder, kosongkan query
                    $query->whereRaw('1 = 0');
                }
            })
            ->emptyState(fn() => view('folders.mekanik.mekanikmedia'))
            ->content(function () {
                return view('folders.mekanik.mekanikmedia');
            })
            ->columns([
                Stack::make([
                    ImageColumn::make('image')
                        ->width('250px')
                        ->height('250px')
                        ->square()
                        ->label('Gambar')
                        ->getStateUsing(function (Mekanikmedia $record) {
                            return $record->getUrl();
                        }),
                ]),
                Stack::make([
                    TextColumn::make('model.name')
                        ->label('Model')
                        ->searchable(),
                    TextColumn::make('collection_name')
                        ->label('Koleksi')
                        ->badge()
                        ->icon('heroicon-o-folder')
                        ->searchable(),
                ]),
                Stack::make([
                    TextColumn::make('name')
                        ->label('Nama')
                        ->searchable(),
                    TextColumn::make('file_name')
                        ->label('Nama File')
                        ->searchable(),
                    TextColumn::make('mime_type')
                        ->label('Tipe MIME')
                        ->searchable(),
                    TextColumn::make('disk')
                        ->label('Disk')
                        ->searchable(),
                    TextColumn::make('conversions_disk')
                        ->label('Disk Konversi')
                        ->searchable(),
                    TextColumn::make('size')
                        ->label('Ukuran')
                        ->numeric()
                        ->searchable()
                        ->formatStateUsing(function ($state) {
                            return $this->formatBytes($state);
                        }),
                    TextColumn::make('order_column')
                        ->label('Urutan')
                        ->numeric(),
                    TextColumn::make('created_at')
                        ->label('Dibuat pada')
                        ->dateTime()

                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('updated_at')
                        ->label('Diubah pada')
                        ->dateTime()
                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([
                "12",
                "24",
                "48",
                "96",
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
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
            'index' => ListMekanikmedia::route('/'),
        ];
    }
}
