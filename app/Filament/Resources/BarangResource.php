<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\Widgets;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Collection;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-s-inbox-stack';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Ketersediaan Barang';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'inventory/barang';

    protected static ?string $pluralModelLabel = 'Ketersediaan Barang';


    protected static ?string $modelLabel = 'Barang';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 0 ? 'success' : 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = static::getModel()::count();
        return $count > 0 ? "Jumlah barang tersedia: $count" : 'Tidak ada barang tersedia';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Barang')
                    ->description('Masukkan informasi identitas barang')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\TextInput::make('serial_number')
                            ->label('Nomor Serial')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Masukkan nomor serial barang')
                            ->prefixIcon('heroicon-m-hashtag')
                            ->columnSpan(['default' => 2, 'md' => 1]),

                        Forms\Components\TextInput::make('kode_barang')
                            ->label('Kode Barang')
                            ->required()
                            ->numeric()
                            ->placeholder('Masukkan kode barang')
                            ->prefixIcon('heroicon-m-qr-code')
                            ->columnSpan(['default' => 2, 'md' => 1]),

                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama barang')
                            ->prefixIcon('heroicon-m-cube')
                            ->columnSpan(['default' => 2, 'md' => 2])
                            ->autocapitalize(),
                    ])
                    ->columns(['default' => 1, 'md' => 2])
                    ->collapsible()
                    ->persistCollapsed(),

                Forms\Components\Section::make('Detail Barang')
                    ->description('Masukkan detail informasi barang')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('jumlah_barang')
                            ->label('Jumlah Barang')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Masukkan jumlah barang')
                            ->columnSpan(['default' => 2, 'md' => 1]),

                        Forms\Components\Select::make('kategori_id')
                            ->label('Kategori')
                            ->relationship('kategori', 'nama_kategori')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih kategori barang')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_kategori')
                                    ->label('Nama Kategori')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama kategori baru'),
                            ])
                            ->createOptionModalHeading('Tambah Kategori Baru')
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                return $action
                                    ->modalHeading('Tambah Kategori Baru')
                                    ->modalSubmitActionLabel('Simpan')
                                    ->modalCancelActionLabel('Batal');
                            })
                            ->prefixIcon('heroicon-m-tag')
                            ->columnSpan(['default' => 2, 'md' => 1]),
                    ])
                    ->columns(['default' => 1, 'md' => 2])
                    ->collapsible()
                    ->persistCollapsed(),
            ])
            ->columns(['default' => 1, 'lg' => 2]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('15s')
            ->defaultGroup('kategori.nama_kategori')
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->wrap(),

                Tables\Columns\TextColumn::make('jumlah_barang')
                    ->label('Jumlah Barang')
                    ->sortable()
                    ->numeric()
                    ->badge()
                    ->alignCenter()
                    ->color(fn(int $state): string => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->icon(fn(int $state): string => $state > 10 ? 'heroicon-m-check-circle' : ($state > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-x-circle')),

                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Sampah')
                    ->indicator('Terhapus')
                    ->trueLabel('Data aktif + terhapus')
                    ->falseLabel('Terhapus'),

                Tables\Filters\SelectFilter::make('stok')
                    ->label('Status Stok')
                    ->options([
                        'kosong' => 'Stok Kosong',
                        'menipis' => 'Stok Menipis',
                        'tersedia' => 'Stok Tersedia',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value']) {
                            'kosong' => $query->where('jumlah_barang', 0),
                            'menipis' => $query->where('jumlah_barang', '>', 0)->where('jumlah_barang', '<', 10),
                            'tersedia' => $query->where('jumlah_barang', '>=', 10),
                            default => $query,
                        };
                    })
                    ->indicator('Status')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->tooltip('Lihat Barang')
                    ->extraAttributes(['class' => 'bg-primary-500/10']),
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit Barang')
                        ->extraAttributes(['class' => 'bg-warning-500/10'])
                        ->color('info'),

                    Action::make('tambah_stok')
                        ->label('+ Stok')
                        ->color('success')
                        ->action(function (Barang $record, array $data): void {
                            $record->update([
                                'jumlah_barang' => $record->jumlah_barang + $data['jumlah'],
                            ]);
                        })

                        ->form([
                            Forms\Components\TextInput::make('jumlah')
                                ->label('Jumlah Stok Tambahan')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1),
                        ])
                        ->modalHeading('Tambah Stok Barang')
                        ->modalSubmitActionLabel('Tambah Stok')
                        ->tooltip('Tambah Stok Barang')
                        ->successNotificationTitle('Stok barang berhasil ditambahkan'),

                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Hapus Barang')
                        ->extraAttributes(['class' => 'bg-danger-500/10']),

                    Tables\Actions\RestoreAction::make()
                        ->tooltip('Pulihkan Barang'),
                ])
            ])
            ->BulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\RestoreBulkAction::make()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('tambah_stok_massal')
                        ->label('Tambah Stok Massal')
                        ->icon('heroicon-m-plus')
                        ->color('success')
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'jumlah_barang' => $record->jumlah_barang + $data['jumlah'],
                                ]);
                            }
                        })
                        ->form([
                            Forms\Components\TextInput::make('jumlah')
                                ->label('Jumlah Stok Tambahan')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Stok barang berhasil ditambahkan'),
                ]),
            ])
            ->defaultSort('nama_barang', 'asc');
    }

     public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Barang')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('serial_number')
                            ->label('Nomor Serial')
                            ->copyable()
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('kode_barang')
                            ->label('Kode Barang')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('nama_barang')
                            ->label('Nama Barang')
                            ->weight(FontWeight::Bold),

                        Infolists\Components\TextEntry::make('jumlah_barang')
                            ->label('Jumlah Barang')
                            ->badge()
                            ->color(fn(int $state): string => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

                        Infolists\Components\TextEntry::make('kategori.nama_kategori')
                            ->label('Kategori')
                            ->badge()
                            ->icon('heroicon-m-tag')
                            ->color('success'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Riwayat')
                    ->icon('heroicon-o-clock')
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i')
                            ->icon('heroicon-m-calendar'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d M Y H:i')
                            ->since()
                            ->icon('heroicon-m-arrow-path'),

                        Infolists\Components\TextEntry::make('deleted_at')
                            ->label('Dihapus Pada')
                            ->dateTime('d M Y H:i')
                            ->badge()
                            ->color('danger')
                            ->icon('heroicon-m-trash')
                            ->visible(fn($record) => $record->deleted_at !== null),
                    ])
                    ->columns(3),
            ])
            ->columns(3);
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
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
