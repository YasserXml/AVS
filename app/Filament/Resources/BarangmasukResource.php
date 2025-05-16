<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangmasukResource\Pages;
use App\Models\Barang;
use App\Models\Barangmasuk;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TrashedFilter;

class BarangmasukResource extends Resource
{
    protected static ?string $model = Barangmasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-end-on-rectangle';

    protected static ?string $navigationGroup = 'Flow Barang';

    protected static ?string $navigationLabel = 'Barang Masuk';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'barang-masuk';

    protected static ?string $modelLabel = 'Barang Masuk';

    protected static ?string $pluralModelLabel = 'Barang Masuk';

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
                Forms\Components\Section::make('Informasi Transaksi')
                    ->description('Detail informasi transaksi barang masuk')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(2)
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_barang_masuk')
                                    ->label('Tanggal Masuk')
                                    ->default(now())
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true)
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
                                    ->prefixIcon('heroicon-o-flag')
                                    ->afterStateUpdated(fn(Set $set) => $set('project_name', null)),

                                // Conditional field for project name when status is "project"
                                Forms\Components\TextInput::make('project_name')
                                    ->label('Nama Project')
                                    ->placeholder('Masukkan nama project')
                                    ->required(fn(Get $get) => $get('status') === 'project')
                                    ->visible(fn(Get $get) => $get('status') === 'project')
                                    ->prefixIcon('heroicon-o-briefcase'),

                                Forms\Components\TextInput::make('dibeli')
                                    ->label('Diajukan Oleh')
                                    ->required()
                                    ->placeholder('Masukkan nama pengaju')
                                    ->prefixIcon('heroicon-o-user-circle'),

                                Forms\Components\Select::make('user_id')
                                    ->label('Yang Input')
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
                                    ->columnSpanFull()
                                    ->afterStateUpdated(function (Set $set) {
                                        // Reset fields when switching between types
                                        $set('barang_id', null);
                                        $set('kode_barang', null);
                                        $set('nama_barang', null);
                                        $set('kategori_id', null);
                                        $set('stok_saat_ini', null);
                                        $set('serial_number', null);
                                    }),
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
                                    ->placeholder('Cari atau pilih barang...')
                                    ->prefixIcon('heroicon-o-magnifying-glass')
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $barang = Barang::find($state);
                                            if ($barang) {
                                                $set('kode_barang', $barang->kode_barang);
                                                $set('stok_saat_ini', $barang->jumlah_barang);
                                                // Clear fields untuk barang baru
                                                $set('nama_barang', null);
                                                $set('kategori_id', null);
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('stok_saat_ini')
                                    ->label('Stok Saat Ini')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefixIcon('heroicon-o-archive-box'),
                            ]),

                        // Bagian input barang baru
                        Forms\Components\Grid::make(2)
                            ->visible(fn(Get $get) => $get('tipe_transaksi') === 'barang_baru')
                            ->schema([
                                Forms\Components\TextInput::make('serial_number')
                                    ->label('Serial Number')
                                    ->required(fn(Get $get) => $get('tipe_transaksi') === 'barang_baru')
                                    ->unique(table: 'barangs', column: 'serial_number', ignoreRecord: true)
                                    ->disabled(fn($operation) => $operation === 'edit')
                                    ->dehydrated()
                                    ->prefixIcon('heroicon-o-identification'),
                                
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
                            ->columns(1)
                            ->schema([
                                Forms\Components\TextInput::make('jumlah_barang_masuk')
                                    ->label('Jumlah Barang Masuk')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->placeholder('Masukkan jumlah barang')
                                    ->prefixIcon('heroicon-o-plus'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('barang.serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('barang.kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('jumlah_barang_masuk')
                    ->label('Jumlah Barang Masuk')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tanggal_barang_masuk')
                    ->label('Tanggal Masuk')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('dibeli')
                    ->label('Diajukan Oleh')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'oprasional_kantor' => 'success',
                        'project' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'oprasional_kantor' => 'Operasional Kantor',
                        'project' => 'Project',
                        default => $state,
                    })
                    ->toggleable(),

                TextColumn::make('project_name')
                    ->label('Nama Project')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visible(fn($livewire) => $livewire->activeTab === 'project'),

                TextColumn::make('user.name')
                    ->label('Yang Input')
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
                TrashedFilter::make(),

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
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_barang_masuk', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_barang_masuk', '<=', $date),
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
                    Tables\Actions\EditAction::make()
                        ->color('info')
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->action(function (Barangmasuk $record) {
                            $record->delete();

                            Notification::make()
                                ->title('Data Barang Masuk Berhasil Dihapus')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\RestoreAction::make()
                        ->color('success')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (Barangmasuk $record) {
                            $record->restore();

                            Notification::make()
                                ->title('Data Barang Masuk Berhasil Dipulihkan')
                                ->success()
                                ->send();
                        }),
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
            ->emptyStateIcon('heroicon-o-archive-box')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Barang Masuk')
                    ->icon('heroicon-o-plus')
                    ->color('success'),
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
}