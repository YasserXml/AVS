<?php

namespace App\Filament\Resources;

use App\Exports\AsetExporter;
use App\Filament\Resources\AsetptResource\Pages;
use App\Filament\Resources\AsetptResource\RelationManagers;
use App\Models\Asetpt;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AsetptResource extends Resource
{
    protected static ?string $model = Asetpt::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $activeNavigationIcon = 'heroicon-s-building-library';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Aset PT';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'inventory/aset-pt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Aset')
                    ->description('Masukkan detail informasi aset dengan lengkap')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->helperText('Tanggal pencatatan aset')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->helperText('Masukkan nama barang yang jelas dan spesifik')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Laptop Dell Inspiron 15')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Auto capitalize first letter of each word
                                $set('nama_barang', ucwords(strtolower($state)));
                            })
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('qty')
                            ->label('Jumlah')
                            ->helperText('Jumlah unit barang')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(999999)
                            ->default(1)
                            ->suffix('unit')
                            ->step(1)
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->collapsible(),

                Forms\Components\Section::make('Detail Tambahan')
                    ->description('Informasi tambahan dan spesifikasi aset')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\TextInput::make('brand')
                            ->label('Merek/Brand')
                            ->helperText('Merek atau brand dari barang')
                            ->maxLength(255)
                            ->placeholder('Contoh: Dell, HP, Canon')
                            ->datalist([
                                'Dell',
                                'HP',
                                'Lenovo',
                                'Asus',
                                'Canon',
                                'Epson',
                                'Samsung',
                                'LG',
                                'Sony',
                                'Panasonic'
                            ])
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('pic')
                            ->label('Penanggung Jawab (PIC) / Lokasi')
                            ->helperText('Nama orang yang bertanggung jawab atau lokasi penyimpanan')
                            ->maxLength(255)
                            ->placeholder('Contoh: Ahmad Wijaya / Ruang IT Lantai 2')
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->label('Status Aset')
                            ->helperText('Status ketersediaan aset')
                            ->options([
                                'stok' => 'Stok Tersedia',
                                'pengembalian' => 'Dalam Pengembalian',
                            ])
                            ->default('stok')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->columnSpan(1),

                        Forms\Components\Select::make('kondisi')
                            ->label('Kondisi Aset')
                            ->helperText('Kondisi fisik aset saat ini')
                            ->options([
                                'baik' => 'Baik',
                                'rusak' => 'Rusak',
                            ])
                            ->default('baik')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Relasi Barang')
                    ->description('Hubungkan dengan data master barang (opsional)')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Forms\Components\Select::make('barang_id')
                            ->label('Pilih dari Master Barang')
                            ->helperText('Pilih barang dari data master jika tersedia')
                            ->relationship('barang', 'nama_barang')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih barang dari master data')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('brand')
                                    ->label('Brand/Merek')
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return \App\Models\Barang::create($data)->id;
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $barang = \App\Models\Barang::find($state);
                                    if ($barang) {
                                        $set('nama_barang', $barang->nama_barang);
                                        if ($barang->brand) {
                                            $set('brand', $barang->brand);
                                        }
                                    }
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->searchable()
                    ->icon('heroicon-m-calendar-days'),

                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->description(fn($record) => $record->brand ? "Merek: {$record->brand}" : null)
                    ->searchable(['nama_barang', 'brand'])
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->wrap()
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->nama_barang;
                    }),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' unit')
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'stok',
                        'warning' => 'pengembalian',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'stok' => 'Stok Tersedia',
                        'pengembalian' => 'Pengembalian',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('kondisi')
                    ->label('Kondisi')
                    ->colors([
                        'success' => 'baik',
                        'danger' => 'rusak',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'baik' => 'Baik',
                        'rusak' => 'Rusak',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('pic')
                    ->label('PIC/Lokasi')
                    ->searchable()
                    ->toggleable()
                    ->limit(30)
                    ->placeholder('Belum ditentukan')
                    ->tooltip(function ($record) {
                        return $record->pic;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since()
                    ->description(fn($record) => $record->created_at->diffForHumans()),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->placeholder('Semua Status')
                    ->options([
                        'stok' => 'Stok Tersedia',
                        'pengembalian' => 'Pengembalian',
                    ])
                    ->native(false)
                    ->multiple(),

                SelectFilter::make('kondisi')
                    ->label('Filter Kondisi')
                    ->placeholder('Semua Kondisi')
                    ->options([
                        'baik' => 'Baik',
                        'rusak' => 'Rusak',
                    ])
                    ->native(false)
                    ->multiple(),

                SelectFilter::make('brand')
                    ->label('Filter Merek')
                    ->placeholder('Semua Merek')
                    ->options(function () {
                        // Assuming you have a method to get unique brands
                        return Asetpt::whereNotNull('brand')
                            ->distinct()
                            ->pluck('brand', 'brand')
                            ->toArray();
                    })
                    ->searchable()
                    ->multiple(),

                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->format('d/m/Y');
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),

                Tables\Filters\TrashedFilter::make()
                    ->label('Data Terhapus')
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail')
                        ->color('gray')
                        ->icon('heroicon-m-eye'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('info')
                        ->icon('heroicon-m-pencil-square'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-m-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Aset')
                        ->modalDescription('Apakah Anda yakin ingin menghapus aset ini? Data akan dipindahkan ke trash.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Pulihkan')
                        ->icon('heroicon-m-arrow-uturn-left'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Permanen')
                        ->icon('heroicon-m-x-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen')
                        ->modalDescription('Apakah Anda yakin? Data akan dihapus permanen dan tidak dapat dipulihkan!')
                        ->modalSubmitActionLabel('Ya, Hapus Permanen'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(ActionSize::Small)
                    ->color('gray')
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export Semua Data')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        return (new AsetExporter())->export();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Export Data Aset')
                    ->modalDescription('Apakah Anda ingin mengexport semua data aset?')
                    ->modalSubmitActionLabel('Ya, Export'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export_selected')
                    ->label('Export Data Terpilih')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Collection $records) {
                        return (new AsetExporter())->export();
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Aset Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus aset yang dipilih?'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen')
                        ->modalDescription('Data akan dihapus permanen dan tidak dapat dipulihkan!'),
                ])
                    ->label('Aksi '),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->recordUrl(null) // Disable row click navigation
            ->searchOnBlur()
            ->deferLoading()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Utama Aset')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        TextEntry::make('tanggal')
                            ->label('Tanggal Pencatatan')
                            ->date('d F Y')
                            ->placeholder('Tidak ada tanggal')
                            ->icon('heroicon-m-calendar-days'),

                        TextEntry::make('nama_barang')
                            ->label('Nama Barang')
                            ->weight(FontWeight::Bold)
                            ->size(TextEntrySize::Large)
                            ->copyable()
                            ->copyMessage('Nama barang disalin!')
                            ->icon('heroicon-m-cube'),

                        TextEntry::make('qty')
                            ->label('Jumlah')
                            ->suffix(' unit')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-m-calculator'),

                        TextEntry::make('brand')
                            ->label('Merek/Brand')
                            ->placeholder('Tidak ada merek')
                            ->copyable()
                            ->icon('heroicon-m-tag'),
                    ])
                    ->columns(2),

                Section::make('Status & Kondisi')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status Aset')
                            ->badge()
                            ->size(TextEntrySize::Large)
                            ->color(fn(string $state): string => match ($state) {
                                'stok' => 'success',
                                'pengembalian' => 'warning',
                                default => 'primary',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'stok' => 'Stok Tersedia',
                                'pengembalian' => 'Dalam Pengembalian',
                                default => ucfirst($state),
                            })
                            ->icon('heroicon-m-check-circle'),

                        TextEntry::make('kondisi')
                            ->label('Kondisi Aset')
                            ->badge()
                            ->size(TextEntrySize::Large)
                            ->color(fn(string $state): string => match ($state) {
                                'baik' => 'success',
                                'rusak' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'baik' => 'Baik',
                                'rusak' => 'Rusak',
                                default => ucfirst($state),
                            })
                            ->icon(fn(string $state): string => match ($state) {
                                'baik' => 'heroicon-m-check-badge',
                                'rusak' => 'heroicon-m-x-circle',
                                default => 'heroicon-m-question-mark-circle',
                            }),

                        TextEntry::make('pic')
                            ->label('Penanggung Jawab (PIC) / Lokasi')
                            ->placeholder('Belum ditentukan')
                            ->copyable()
                            ->icon('heroicon-m-user-circle'),
                    ])
                    ->columns(3),

                Section::make('Catatan Tambahan')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Tidak ada keterangan tambahan')
                            ->prose()
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => !empty($record->keterangan))
                    ->collapsible(),

                Section::make('Informasi Sistem')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y, H:i:s')
                            ->since()
                            ->icon('heroicon-m-plus-circle'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d F Y, H:i:s')
                            ->since()
                            ->icon('heroicon-m-pencil-square'),

                        TextEntry::make('deleted_at')
                            ->label('Dihapus Pada')
                            ->dateTime('d F Y, H:i:s')
                            ->placeholder('Tidak dihapus')
                            ->icon('heroicon-m-trash')
                            ->color('danger'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsetpts::route('/'),
            'create' => Pages\CreateAsetpt::route('/create'),
            'edit' => Pages\EditAsetpt::route('/{record}/edit'),
        ];
    }
}
