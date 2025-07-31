<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisResource\Pages;
use App\Filament\Resources\JenisResource\RelationManagers;
use App\Models\Jenis;
use App\Models\Kategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JenisResource extends Resource
{
    protected static ?string $model = Jenis::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $activeNavigationIcon = 'heroicon-s-bookmark';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Jenis Barang';

    protected static ?string $label = 'Jenis Barang';

    protected static ?string $pluralLabel = 'Jenis Barang';

    protected static ?string $slug = 'inventory/jenis';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jenis Barang')
                    ->description('Masukkan informasi dasar jenis barang')
                    ->schema([
                        Forms\Components\Select::make('kategori_id')
                            ->label('Kategori')
                            ->relationship(name: 'kategori', titleAttribute: 'nama_kategori')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih kategori barang')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('nama_jenis', null);
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_kategori')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Nama Kategori Baru'),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Kategori::create($data)->getKey();
                            }),

                        Forms\Components\TextInput::make('nama_jenis')
                            ->label('Nama Jenis')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Laptop, Smartphone, dll')
                            ->helperText('Masukkan nama spesifik jenis barang')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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

                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('nama_jenis')
                    ->label('Nama Jenis')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn($record): ?string => $record->deskripsi),

                Tables\Columns\TextColumn::make('barang_count')
                    ->label('Jumlah Barang')
                    ->badge()
                    ->alignCenter()
                    ->color('success')
                    ->icon('heroicon-m-cube')
                    ->counts('barang')
                    ->tooltip('Total barang dengan jenis ini')
                    ->sortable(),

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

                Tables\Filters\SelectFilter::make('kategori_id')
                    ->label('Filter Berdasarkan Kategori')
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Kategori')
                    ->indicator('Kategori'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->tooltip('Lihat Detail Jenis'),

                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit Jenis'),

                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Hapus Jenis'),

                    Tables\Actions\RestoreAction::make()
                        ->tooltip('Pulihkan Jenis'),

                    Tables\Actions\ForceDeleteAction::make()
                        ->tooltip('Hapus Permanen'),

                    // Action untuk melihat barang dengan jenis ini
                    Tables\Actions\Action::make('lihat_barang')
                        ->label('Lihat Barang')
                        ->icon('heroicon-m-eye')
                        ->color('info')
                        ->url(fn($record): string => route('filament.admin.resources.inventory.barang.index', [
                            'tableFilters' => [
                                'jenis' => [
                                    'value' => $record->id
                                ]
                            ]
                        ]))
                        ->openUrlInNewTab()
                        ->tooltip('Lihat semua barang dengan jenis ini')
                        ->visible(fn($record) => $record->barang_count > 0),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('kategori.nama_kategori', 'asc')
            ->paginated([10, 25, 50, 100, 'all'])
            ->recordUrl(null) // Disable default view URL
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['kategori'])->withCount('barang');
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
            'index' => Pages\ListJenis::route('/'),
            'create' => Pages\CreateJenis::route('/create'),
            'edit' => Pages\EditJenis::route('/{record}/edit'),
        ];
    }
}
