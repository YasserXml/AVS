<?php

namespace App\Filament\Resources;

use App\Exports\BarangExporter;
use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\Widgets;
use App\Models\Barang;
use App\Models\Kategori;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $activeNavigationIcon = 'heroicon-s-inbox-stack';

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
                            ->columnSpan(['default' => 2, 'md' => 1])
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset spesifikasi ketika kategori berubah
                                $set('spesifikasi', null);
                            }),
                    ])
                    ->columns(['default' => 1, 'md' => 2])
                    ->collapsible()
                    ->persistCollapsed(),

                // Section Spesifikasi - Sekarang menyimpan ke database
                Forms\Components\Section::make('Spesifikasi Barang')
                    ->description('Masukkan spesifikasi detail barang')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                // Spesifikasi untuk Komputer
                                Forms\Components\Group::make()
                                    ->schema([
                                        // PENTING: Gunakan nama field yang flat, bukan nested
                                        Forms\Components\TextInput::make('spec_processor')
                                            ->label('Processor')
                                            ->placeholder('Contoh: Intel Core i5-12400F')
                                            ->prefixIcon('heroicon-m-cpu-chip'),

                                        Forms\Components\TextInput::make('spec_ram')
                                            ->label('RAM')
                                            ->placeholder('Contoh: 16GB DDR4')
                                            ->prefixIcon('heroicon-m-cpu-chip'),

                                        Forms\Components\TextInput::make('spec_storage')
                                            ->label('Storage')
                                            ->placeholder('Contoh: 512GB SSD')
                                            ->prefixIcon('heroicon-m-server-stack'),

                                        Forms\Components\TextInput::make('spec_vga')
                                            ->label('VGA/GPU')
                                            ->placeholder('Contoh: NVIDIA RTX 4060')
                                            ->prefixIcon('heroicon-m-computer-desktop'),

                                        Forms\Components\TextInput::make('spec_motherboard')
                                            ->label('Motherboard')
                                            ->placeholder('Contoh: ASUS B550M-A')
                                            ->prefixIcon('heroicon-m-cog-8-tooth'),

                                        Forms\Components\TextInput::make('spec_psu')
                                            ->label('Power Supply')
                                            ->placeholder('Contoh: 650W 80+ Bronze')
                                            ->prefixIcon('heroicon-m-bolt'),
                                    ])
                                    ->columns(2)
                                    ->visible(function (callable $get) {
                                        $kategoriId = $get('kategori_id');
                                        if (!$kategoriId) return false;
                                        $kategori = Kategori::find($kategoriId);
                                        return $kategori && strtolower($kategori->nama_kategori) === 'komputer';
                                    }),

                                // Spesifikasi untuk Elektronik
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('spec_brand')
                                            ->label('Merek')
                                            ->placeholder('Contoh: Samsung, LG, Sony')
                                            ->prefixIcon('heroicon-m-building-storefront'),

                                        Forms\Components\TextInput::make('spec_model')
                                            ->label('Model')
                                            ->placeholder('Contoh: Galaxy S24 Ultra')
                                            ->prefixIcon('heroicon-m-device-phone-mobile'),

                                        Forms\Components\TextInput::make('spec_garansi')
                                            ->label('Garansi')
                                            ->placeholder('Contoh: 2 Tahun')
                                            ->prefixIcon('heroicon-m-shield-check'),
                                    ])
                                    ->columns(2)
                                    ->visible(function (callable $get) {
                                        $kategoriId = $get('kategori_id');
                                        if (!$kategoriId) return false;
                                        $kategori = Kategori::find($kategoriId);
                                        return $kategori && strtolower($kategori->nama_kategori) === 'elektronik';
                                    }),
                            ])
                            ->visible(fn(callable $get) => $get('kategori_id') !== null),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->reactive()
                    ->live(),
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
                    ->searchable(isIndividual: true)
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
                    ->searchable(isIndividual: true)
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
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Sampah')
                    ->indicator('Terhapus')
                    ->trueLabel('Data aktif + terhapus')
                    ->falseLabel('Terhapus')
                    ->preload()
                    ->searchable(),

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
                        ->visible(
                            fn() => Auth::user()->hasAnyRole(['super_admin', 'administrator'])
                        )
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
            ->headerActions([
                // Action untuk export semua data
                Tables\Actions\Action::make('export_all')
                    ->label('Export Semua Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(
                        fn() => Auth::user()->hasAnyRole(['super_admin', 'administrator'])
                    )
                    ->action(function () {
                        return (new BarangExporter())->export(); // Tanpa parameter = semua data
                    }),
            ])
            ->BulkActions([
                Tables\Actions\BulkAction::make('export_selected')
                    ->label('Export Terpilih')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(
                        fn() => Auth::user()->hasAnyRole(['super_admin', 'administrator'])
                    )
                    ->action(function (Collection $records) {
                        return (new BarangExporter())->export($records); // Dengan parameter = data terpilih
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Data barang terpilih berhasil diekspor'),
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
                        ->visible(
                            fn() => Auth::user()->hasAnyRole(['super_admin', 'administrator'])
                        )
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
