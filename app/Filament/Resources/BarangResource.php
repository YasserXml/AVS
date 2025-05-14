<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Filament\Resources\BarangResource\Widgets;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use App\Models\Kategori;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Collection;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

   protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Ketersediaan Barang';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'barang';

    protected static ?string $pluralModelLabel = 'Ketersediaan Barang';

    protected static ?string $recordTitleAttribute = 'nama_barang';

    protected static ?string $modelLabel = 'Barang';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string
    {
        $jumlahStokKritis = static::getModel()::where('jumlah_barang', '<=', 10)->count();
        
        if ($jumlahStokKritis > 0) {
            return 'danger';
        }
        
        return 'success';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['serial_number', 'nama_barang', 'kode_barang'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Kategori' => $record->kategori->nama_kategori,
            'Stok' => $record->jumlah_barang,
        ];
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total jumlah barang yang tersedia';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Barang')
                            ->description('Masukkan detail informasi barang')
                            ->icon('heroicon-o-information-circle')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('serial_number')
                                    ->label('Nomor Serial')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Masukkan nomor serial barang')
                                    ->autocapitalize('characters')
                                    ->prefixIcon('heroicon-m-identification')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('kode_barang')
                                    ->label('Kode Barang')
                                    ->required()
                                    ->numeric()
                                    ->placeholder('Masukkan kode barang')
                                    ->prefixIcon('heroicon-m-hashtag')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama barang')
                                    ->prefixIcon('heroicon-m-tag')
                                    ->columnSpan(2),

                                Forms\Components\Select::make('kategori_id')
                                    ->label('Kategori')
                                    ->relationship('kategori', 'nama_kategori')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama_kategori')
                                            ->label('Nama Kategori')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\ColorPicker::make('warna')
                                            ->label('Warna Label')
                                            ->required(),
                                    ])
                                    ->prefixIcon('heroicon-m-squares-2x2')
                                    ->columnSpan(2),
                                    
                                Forms\Components\Textarea::make('deskripsi')
                                    ->label('Deskripsi')
                                    ->placeholder('Masukkan deskripsi barang')
                                    ->autosize()
                                    ->columnSpan(2),
                            ]),
                        
                        Forms\Components\Section::make('Detail Inventaris')
                            ->description('Masukkan detail jumlah dan kondisi barang')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('jumlah_barang')
                                    ->label('Jumlah Barang')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('Masukkan jumlah barang')
                                    ->suffixIcon('heroicon-m-cube')
                                    ->columnSpan(1),
                                    
                                Forms\Components\Select::make('status')
                                    ->label('Status Barang')
                                    ->options([
                                        'tersedia' => 'Tersedia',
                                        'terbatas' => 'Stok Terbatas',
                                        'habis' => 'Stok Habis',
                                        'rusak' => 'Rusak/Cacat',
                                    ])
                                    ->default('tersedia')
                                    ->required()
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('lokasi_penyimpanan')
                                    ->label('Lokasi Penyimpanan')
                                    ->placeholder('Masukkan lokasi penyimpanan barang')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->columnSpan(2),
                                    
                                Forms\Components\DatePicker::make('tanggal_pengadaan')
                                    ->label('Tanggal Pengadaan')
                                    ->placeholder('Pilih tanggal pengadaan barang')
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->date()
                                    ->reactive()
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('harga_satuan')
                                    ->label('Harga Satuan (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                    
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Gambar & Media')
                            ->schema([
                                Forms\Components\FileUpload::make('gambar')
                                    ->label('Foto Barang')
                                    ->image()
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeMode('cover')
                                    ->maxSize(5120)
                                    ->directory('barang-images')
                                    ->imageEditor()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make('Informasi Barang')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('serial_number')
                                            ->label('Nomor Serial')
                                            ->copyable()
                                            ->icon('heroicon-m-identification'),
                                            
                                        Infolists\Components\TextEntry::make('kode_barang')
                                            ->label('Kode Barang')
                                            ->copyable()
                                            ->icon('heroicon-m-hashtag'),
                                            
                                        Infolists\Components\TextEntry::make('nama_barang')
                                            ->label('Nama Barang')
                                            ->weight(FontWeight::Bold)
                                            ->columnSpan(2)
                                            ->icon('heroicon-m-tag'),
                                            
                                        Infolists\Components\TextEntry::make('kategori.nama_kategori')
                                            ->label('Kategori')
                                            ->badge()
                                            ->icon('heroicon-m-squares-2x2'),
                                            
                                        Infolists\Components\TextEntry::make('tanggal_pengadaan')
                                            ->label('Tanggal Pengadaan')
                                            ->date('d M Y')
                                            ->icon('heroicon-m-calendar'),
                                    ]),
                                    
                                Infolists\Components\TextEntry::make('deskripsi')
                                    ->label('Deskripsi')
                                    ->markdown()
                                    ->columnSpan(2),
                            ]),
                            
                        Infolists\Components\Section::make('Detail Inventaris')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('jumlah_barang')
                                            ->label('Jumlah Barang')
                                            ->icon('heroicon-m-cube')
                                            ->color(fn (Model $record): string => 
                                                match(true) {
                                                    $record->jumlah_barang <= 0 => 'danger',
                                                    $record->jumlah_barang < 10 => 'warning',
                                                    default => 'success',
                                                }
                                            ),
                                            
                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn (Model $record): string => 
                                                match($record->status) {
                                                    'tersedia' => 'success',
                                                    'terbatas' => 'warning',
                                                    'habis' => 'danger',
                                                    'rusak' => 'gray',
                                                    default => 'primary',
                                                }
                                            ),
                                            
                                        Infolists\Components\TextEntry::make('harga_satuan')
                                            ->label('Harga Satuan')
                                            ->money('IDR'),
                                            
                                        Infolists\Components\TextEntry::make('lokasi_penyimpanan')
                                            ->label('Lokasi Penyimpanan')
                                            ->icon('heroicon-m-map-pin')
                                            ->columnSpan(3),
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                    
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make('Gambar Barang')
                            ->schema([
                                Infolists\Components\ImageEntry::make('gambar')
                                    ->label('')
                                    ->disk('public')
                                    ->height(300)
                                    ->extraImgAttributes([
                                        'class' => 'rounded-lg shadow',
                                    ]),
                            ]),
                            
                        Infolists\Components\Section::make('Metadata')
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d M Y, H:i')
                                    ->icon('heroicon-m-clock'),
                                    
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime('d M Y, H:i')
                                    ->icon('heroicon-m-arrow-path'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->size(40)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Nomor Serial')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor serial disalin!')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-m-identification'),
                
                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-hashtag'),
                
                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->tooltip(function (Model $record): string {
                        return $record->nama_barang;
                    })
                    ->icon('heroicon-m-tag')
                    ->weight(FontWeight::Medium),
                
                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->sortable()
                    ->badge()
                    ->color(fn (Model $record): ?string => $record->kategori?->warna),
                
                Tables\Columns\TextColumn::make('jumlah_barang')
                    ->label('Jumlah')
                    ->sortable()
                    ->alignCenter()
                    ->icon('heroicon-m-cube')
                    ->color(function (Model $record): string {
                        if ($record->jumlah_barang <= 0) {
                            return 'danger';
                        } elseif ($record->jumlah_barang < 10) {
                            return 'warning';
                        }
                        
                        return 'success';
                    }),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Model $record): string => 
                        match($record->status) {
                            'tersedia' => 'success',
                            'terbatas' => 'warning',
                            'habis' => 'danger',
                            'rusak' => 'gray',
                            default => 'primary',
                        }
                    ),
                    
                Tables\Columns\TextColumn::make('lokasi_penyimpanan')
                    ->label('Lokasi')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama_kategori')
                    ->preload()
                    ->multiple()
                    ->searchable(),

                TrashedFilter::make()
                    ->label('Tong Sampah'),
                    
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'tersedia' => 'Tersedia',
                        'terbatas' => 'Stok Terbatas',
                        'habis' => 'Stok Habis',
                        'rusak' => 'Rusak/Cacat',
                    ]),
                    
                Tables\Filters\Filter::make('stok_kosong')
                    ->label('Stok Kosong')
                    ->query(fn (Builder $query): Builder => $query->where('jumlah_barang', 0)),
                
                Tables\Filters\Filter::make('stok_menipis')
                    ->label('Stok Menipis')
                    ->query(fn (Builder $query): Builder => $query->where('jumlah_barang', '>', 0)->where('jumlah_barang', '<', 10)),
                
                Tables\Filters\Filter::make('created_at')
                    ->label('Periode Pendaftaran')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->filtersFormColumns(3)
            ->groups([
                Group::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->titlePrefixedWithLabel(false),
                    
                Group::make('status')
                    ->label('Status'),
                    
                Group::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->tooltip('Lihat detail barang'),
                    
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->tooltip('Edit barang'),
                    
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('tambah_stok')
                        ->label('Tambah Stok')
                        ->icon('heroicon-o-plus')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\TextInput::make('jumlah')
                                ->label('Jumlah Penambahan')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('Masukkan jumlah penambahan'),
                        ])
                        ->action(function (Barang $record, array $data): void {
                            $record->jumlah_barang += $data['jumlah'];
                            
                            // Update status jika stok kosong menjadi tersedia
                            if ($record->status === 'habis' && $record->jumlah_barang > 0) {
                                $record->status = $record->jumlah_barang < 10 ? 'terbatas' : 'tersedia';
                            }
                            
                            $record->save();
                            
                            Notification::make()
                                ->title('Stok berhasil ditambahkan')
                                ->body('Stok ' . $record->nama_barang . ' berhasil ditambahkan sebanyak ' . $data['jumlah'] . ' unit.')
                                ->success()
                                ->send();
                        }),
                        
                    Tables\Actions\Action::make('kurangi_stok')
                        ->label('Kurangi Stok')
                        ->icon('heroicon-o-minus')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\TextInput::make('jumlah')
                                ->label('Jumlah Pengurangan')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('Masukkan jumlah pengurangan'),
                        ])
                        ->action(function (Barang $record, array $data): void {
                            $record->jumlah_barang = max(0, $record->jumlah_barang - $data['jumlah']);
                            
                            // Update status berdasarkan jumlah stok
                            if ($record->jumlah_barang <= 0) {
                                $record->status = 'habis';
                            } elseif ($record->jumlah_barang < 10) {
                                $record->status = 'terbatas';
                            }
                            
                            $record->save();
                            
                            Notification::make()
                                ->title('Stok berhasil dikurangi')
                                ->body('Stok ' . $record->nama_barang . ' berhasil dikurangi sebanyak ' . $data['jumlah'] . ' unit.')
                                ->success()
                                ->send();
                        }),
                        
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash'),
                        
                    Tables\Actions\RestoreAction::make()
                        ->label('Pulihkan')
                        ->icon('heroicon-o-arrow-path'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash'),
                    
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih')
                        ->icon('heroicon-o-arrow-path'),
                    
                    Tables\Actions\BulkAction::make('update_stock')
                        ->label('Update Jumlah Barang')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('action')
                                ->label('Aksi')
                                ->options([
                                    'add' => 'Tambah Jumlah',
                                    'subtract' => 'Kurangi Jumlah',
                                    'set' => 'Tetapkan Jumlah',
                                ])
                                ->required(),
                            
                            Forms\Components\TextInput::make('amount')
                                ->label('Jumlah')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function (array $data, Collection $records): void {
                            foreach ($records as $record) {
                                $semula = $record->jumlah_barang;
                                
                                if ($data['action'] === 'add') {
                                    $record->jumlah_barang += $data['amount'];
                                } elseif ($data['action'] === 'subtract') {
                                    $record->jumlah_barang = max(0, $record->jumlah_barang - $data['amount']);
                                } else {
                                    $record->jumlah_barang = $data['amount'];
                                }
                                
                                // Update status berdasarkan jumlah stok
                                if ($record->jumlah_barang <= 0) {
                                    $record->status = 'habis';
                                } elseif ($record->jumlah_barang < 10) {
                                    $record->status = 'terbatas';
                                } else {
                                    $record->status = 'tersedia';
                                }
                                
                                $record->save();
                            }
                            
                            Notification::make()
                                ->title('Stok barang berhasil diperbarui')
                                ->body('Stok dari ' . $records->count() . ' barang berhasil diperbarui.')
                                ->success()
                                ->send();
                        }),
                        
                    Tables\Actions\BulkAction::make('export')
                        ->label('Ekspor ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records): void {
                            // Logika untuk ekspor ke Excel
                            // Anda perlu mengimplementasikan ekspor dengan paket seperti Laravel Excel
                            Notification::make()
                                ->title('Ekspor Berhasil')
                                ->body('Data berhasil diekspor ke Excel')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Barang Baru')
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->defaultSort('created_at', 'desc')
            ->groupingSettingsInDropdownOnDesktop()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\RiwayatBarangRelationManager::class,
        ];
    }

    // public static function getWidgets(): array
    // {
    //     return [
    //         Widgets\BarangStatsOverview::class,
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            // 'view' => Pages\ViewBarang::route('/{record}'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}