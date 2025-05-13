<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
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
use Illuminate\Support\Collection;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Ketersediaan Barang';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'ketersediaan-barang';

     public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
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
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kode_barang')
                            ->label('Kode Barang')
                            ->required()
                            ->numeric()
                            ->placeholder('Masukkan kode barang')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama barang')
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
                                Forms\Components\Textarea::make('deskripsi')
                                    ->label('Deskripsi')
                                    ->maxLength(1000),
                            ])
                            ->columnSpan(2),
                    ]),
                
                Forms\Components\Section::make('Detail Inventaris')
                    ->description('Masukkan detail jumlah dan harga barang')
                    ->icon('heroicon-o-currency-dollar')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('jumlah_barang')
                            ->label('Jumlah Barang')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Masukkan jumlah barang')
                            ->suffixIcon('heroicon-o-cube')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('harga_barang')
                            ->label('Harga Barang (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->placeholder('Masukkan harga satuan barang')
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('total_harga')
                            ->label('Total Nilai Inventaris')
                            ->content(function (Forms\Get $get): string {
                                $jumlah = (int) $get('jumlah_barang');
                                $harga = (int) $get('harga_barang');
                                $total = $jumlah * $harga;
                                
                                return 'Rp ' . number_format($total, 0, ',', '.');
                            })
                            ->columnSpan(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Nomor Serial')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor serial disalin!')
                    ->copyMessageDuration(1500),
                
                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->tooltip(function (Model $record): string {
                        return $record->nama_barang;
                    }),
                
                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('jumlah_barang')
                    ->label('Jumlah')
                    ->sortable()
                    ->alignCenter()
                    ->color(function (Model $record): string {
                        if ($record->jumlah_barang <= 0) {
                            return 'danger';
                        } elseif ($record->jumlah_barang < 10) {
                            return 'warning';
                        }
                        
                        return 'success';
                    }),
                
                Tables\Columns\TextColumn::make('harga_barang')
                    ->label('Harga Satuan')
                    ->sortable()
                    ->money('IDR')
                    ->alignRight(),
                
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
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash'),
                    
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
                                if ($data['action'] === 'add') {
                                    $record->jumlah_barang += $data['amount'];
                                } elseif ($data['action'] === 'subtract') {
                                    $record->jumlah_barang = max(0, $record->jumlah_barang - $data['amount']);
                                } else {
                                    $record->jumlah_barang = $data['amount'];
                                }
                                
                                $record->save();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->groupingSettingsInDropdownOnDesktop()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
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
