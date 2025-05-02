<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangmasukResource\Pages;
use App\Filament\Resources\BarangmasukResource\RelationManagers;
use App\Models\Barang;
use App\Models\Barangmasuk;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangmasukResource extends Resource
{
    protected static ?string $model = Barangmasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-end-on-rectangle';

    protected static ?string $navigationGroup = 'Flow Barang';

    protected static ?string $navigationLabel = 'Barang Masuk';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'barang-masuk';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

   
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'serial_number',
            'barang.nama_barang',
            'kode_barang',
            'jumlah_barang_masuk',
            'harga_barang',
            'total_harga',
            'tanggal_masuk_barang',
            'status',
            'user.name',
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->description('Detail informasi transaksi barang masuk')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('serial_number')
                                    ->label('Serial Number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->dehydrated()
                                    ->prefixIcon('heroicon-o-identification'),
    
                                Forms\Components\DatePicker::make('tanggal_barang_masuk')
                                    ->label('Tanggal Masuk')
                                    ->default(now())
                                    ->required()
                                    ->prefixIcon('heroicon-o-calendar'),
    
                                Forms\Components\Select::make('status')
                                    ->label('Status Penggunaan')
                                    ->options([
                                        'oprasional_kantor' => 'Operasional Kantor',
                                        'project' => 'Project',
                                    ])
                                    ->default('oprasional_kantor')
                                    ->required()
                                    ->native(false)
                                    ->reactive()
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-flag'),
    
                                Forms\Components\Select::make('user_id')
                                    ->label('Petugas')
                                    ->relationship('user', 'name')
                                    ->default(fn() => filament()->auth()->id())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-o-user'),
                            ]),
    
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Tambahkan catatan atau keterangan jika diperlukan')
                            ->columnSpanFull(),
                    ]),
    
                Forms\Components\Section::make('Detail Barang')
                    ->description('Informasi detail barang yang masuk')
                    ->icon('heroicon-o-cube')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\Radio::make('tipe_transaksi')  
                                            ->label('Jenis Input Barang')
                                            ->options([
                                                'barang_lama' => 'Pilih Barang yang Sudah Ada',
                                                'barang_baru' => 'Tambah Barang Baru',
                                            ])
                                            ->default('barang_lama')
                                            ->required()
                                            ->inline()
                                            ->live()
                                            ->helperText('Pilih jenis input sesuai kebutuhan transaksi barang masuk')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
    
                        // Bagian pilih barang yang sudah ada
                        Forms\Components\Grid::make(2)
                            ->visible(fn(Get $get) => $get('tipe_transaksi') === 'barang_lama')
                            ->schema([
                                Forms\Components\Select::make('barang_id')
                                    ->label('Pilih Barang')
                                    ->relationship('barang', 'nama_barang')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->dehydrated()
                                    ->required(fn(Get $get) => $get('tipe_transaksi') === 'barang_lama')
                                    ->placeholder('Cari dan pilih barang...')
                                    ->prefixIcon('heroicon-o-magnifying-glass')
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $barang = Barang::find($state);
                                            if ($barang) {
                                                $set('kode_barang', $barang->kode_barang);
                                                $set('harga_barang', $barang->harga_barang ?? 0);
                                                // Clear fields untuk barang baru
                                                $set('nama_barang', null);
                                                $set('kategori_id', null);
                                            }
                                        }
                                    }),
                            ]),
    
                        // Bagian input barang baru
                        Forms\Components\Grid::make(2)
                            ->visible(fn(Get $get) => $get('tipe_transaksi') === 'barang_baru')
                            ->schema([
                                Forms\Components\TextInput::make('kode_barang')
                                    ->label('Kode Barang')
                                    ->numeric()
                                    ->unique(table: 'barangs', column: 'kode_barang', ignoreRecord: true)
                                    ->required(fn(Get $get) => $get('tipe_transaksi') === 'barang_baru')
                                    ->dehydrated()
                                    ->prefixIcon('heroicon-o-identification'),
    
                                Forms\Components\TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->required(fn(Get $get) => $get('tipe_transaksi') === 'barang_baru')
                                    ->placeholder('Masukkan nama barang')
                                    ->dehydrated()
                                    ->prefixIcon('heroicon-o-tag'),
    
                                Forms\Components\Select::make('kategori_id')
                                    ->label('Kategori Barang')
                                    ->relationship('kategori', 'nama_kategori')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama_kategori')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('Nama Kategori'),
                                    ])
                                    ->required(fn(Get $get) => $get('tipe_transaksi') === 'barang_baru')
                                    ->prefixIcon('heroicon-o-tag'),
                            ]),
    
                        Forms\Components\Grid::make()
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('jumlah_barang_masuk')
                                    ->label('Jumlah Barang Masuk')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->placeholder('Masukkan jumlah barang')
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $harga = $get('harga_barang') ?? 0;
                                        $jumlah = $state ?? 0;
                                        $set('total_harga', $harga * $jumlah);
                                    })
                                    ->prefixIcon('heroicon-o-plus'),
    
                                Forms\Components\TextInput::make('harga_barang')
                                    ->label('Harga barang')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->live()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $numericValue = preg_replace('/[^0-9]/', '', $state);
                                        $set('harga_barang', $numericValue);
                                        
                                        $jumlah = $get('jumlah_barang_masuk') ?? 0;
                                        $set('total_harga', $numericValue * $jumlah);
                                    })
                                    ->prefixIcon('heroicon-o-banknotes'),
    
                                Forms\Components\TextInput::make('total_harga')
                                    ->label('Total Harga')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Total harga akan dihitung otomatis')
                                    ->prefixIcon('heroicon-o-calculator'),
                            ]),
                    ]),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('serial_number')
                    ->label('Nomor Transaksi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
    
                TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
    
                TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->toggleable(),
    
                TextColumn::make('jumlah_barang_masuk')
                    ->label('Jumlah')
                    ->sortable()
                    ->toggleable(),
    
                TextColumn::make('harga_barang')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
    
                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
    
                TextColumn::make('tanggal_barang_masuk')
                    ->label('Tanggal Masuk')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
    
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'oprasional_kantor' => 'primary',
                        'project' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'oprasional_kantor' => 'Operasional Kantor',
                        'project' => 'Project',
                        default => $state,
                    })
                    ->toggleable(),
    
                TextColumn::make('user.name')
                    ->label('Petugas')
                    ->sortable()
                    ->toggleable(),
    
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
    
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Penggunaan')
                    ->options([
                        'oprasional_kantor' => 'Operasional Kantor',
                        'project' => 'Project',
                    ])
                    ->multiple(),
                
                Filter::make('tanggal_barang_masuk')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_barang_masuk', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_barang_masuk', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators['dari_tanggal'] = 'Dari tanggal ' . Carbon::parse($data['dari_tanggal'])->format('d M Y');
                        }
                        
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators['sampai_tanggal'] = 'Sampai tanggal ' . Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                        }
                        
                        return $indicators;
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Data Barang Masuk')
            ->emptyStateDescription('Silakan tambahkan data barang masuk dengan klik tombol di bawah')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Barang Masuk'),
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
            'index' => Pages\ListBarangmasuks::route('/'),
            'create' => Pages\CreateBarangmasuk::route('/create'),
            'edit' => Pages\EditBarangmasuk::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
