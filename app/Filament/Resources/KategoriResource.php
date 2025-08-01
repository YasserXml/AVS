<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriResource\Pages;
use App\Filament\Resources\KategoriResource\RelationManagers;
use App\Models\Kategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KategoriResource extends Resource
{
    protected static ?string $model = Kategori::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $activeNavigationIcon = 'heroicon-s-bookmark';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Kategori Barang';

    protected static ?string $label = 'Kategori';

    protected static ?string $pluralLabel = 'Kategori';

    protected static ?string $slug = 'inventory/kategori';

    // protected static ?string $modelLabel = 'Kategori Barang';

    protected static ?string $pluralModelLabel = 'Kategori Barang';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori')
                    ->description('Masukkan informasi dasar kategori')
                    ->schema([
                        Forms\Components\TextInput::make('nama_kategori')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->placeholder('Masukkan nama kategori')
                            ->label('Nama Kategori')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn($record): string => route('filament.admin.resources.inventory.barang.index', [
                    'tableFilters' => [
                        'kategori' => [
                            'value' => $record->id
                        ]
                    ]
                ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No')
                    ->state(static function ($rowLoop): string {
                        return (string) $rowLoop->iteration;
                    })
                    ->alignCenter()
                    ->color('gray')
                    ->weight(FontWeight::Bold)
                    ->searchable(false)
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Dihapus'),

                // Filter berdasarkan status stok
                Tables\Filters\SelectFilter::make('status_stok')
                    ->label('Status Stok')
                    ->options([
                        'kosong' => 'Stok Kosong',
                        'menipis' => 'Stok Menipis',
                        'tersedia' => 'Stok Tersedia',
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }

                        return $query->withSum('barangAktif as total_stok', 'jumlah_barang')
                            ->having('total_stok', match ($data['value']) {
                                'kosong' => '=',
                                'menipis' => '<',
                                'tersedia' => '>=',
                                default => '>='
                            }, match ($data['value']) {
                                'kosong' => 0,
                                'menipis' => 50, // Threshold bisa disesuaikan
                                'tersedia' => 50,
                                default => 0
                            });
                    })
                    ->indicator('Status Stok')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->tooltip('Lihat Detail Kategori'),

                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit Kategori'),

                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Hapus Kategori'),

                    Tables\Actions\RestoreAction::make()
                        ->tooltip('Pulihkan Kategori'),

                    Tables\Actions\ForceDeleteAction::make()
                        ->tooltip('Hapus Permanen'),

                    // Action untuk melihat semua barang dalam kategori
                    Tables\Actions\Action::make('lihat_barang')
                        ->label('Lihat Barang')
                        ->icon('heroicon-m-eye')
                        ->color('info')
                        ->url(fn($record): string => route('filament.admin.resources.inventory.barang.index', [
                            'tableFilters' => [
                                'kategori' => [
                                    'value' => $record->id
                                ]
                            ]
                        ]))
                        ->openUrlInNewTab()
                        ->tooltip('Lihat semua barang dalam kategori ini'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nama_kategori', 'asc')
            ->paginated([10, 25, 50, 100, 'all'])
            // Tambahkan eager loading untuk performa yang lebih baik
            ->modifyQueryUsing(function ($query) {
                return $query->withCount('barangAktif as total_barang')
                    ->withSum('barangAktif as total_stok', 'jumlah_barang');
            });
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
            'index' => Pages\ListKategoris::route('/'),
            'create' => Pages\CreateKategori::route('/create'),
            'edit' => Pages\EditKategori::route('/{record}/edit'),
        ];
    }
}
