<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeuanganmediaResource\Pages;
use App\Filament\Resources\KeuanganmediaResource\RelationManagers;
use App\Models\Keuanganfolder;
use App\Models\Keuanganmedia;
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

class KeuanganmediaResource extends Resource
{
    protected static ?string $model = Keuanganmedia::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function setBreadcrumb(?string $breadcrumb): void
    {
        self::$breadcrumb = $breadcrumb;
    }

    public static function getSlug(): string
    {
        return 'arsip/keuangan/folder';
    }

    public static function getNavigationSort(): ?int
    {
        return 16;
    }

    public static function getUrlFromFolderKeuangan(Keuanganfolder $folder, string $name = 'index'): string
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
                    $folder = Keuanganfolder::where('slug', $folderSlug)->first();

                    if ($folder && $folder->canBeAccessedBy()) {
                        $query->where('model_type', Keuanganfolder::class)
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
                    $folder = Keuanganfolder::find($folderId);

                    if ($folder && $folder->canBeAccessedBy()) {
                        $query->where('model_type', Keuanganfolder::class)
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
            ->emptyState(fn() => view('folders.keuangan.keuanganmedia'))
            ->content(function () {
                return view('folders.keuangan.keuanganmedia');
            })
            ->columns([
                Stack::make([
                    ImageColumn::make('image')
                        ->width('250px')
                        ->height('250px')
                        ->square()
                        ->label('Gambar')
                        ->getStateUsing(function (Keuanganmedia $record) {
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
            'index' => Pages\ListKeuanganmedia::route('/'),
        ];
    }
}
