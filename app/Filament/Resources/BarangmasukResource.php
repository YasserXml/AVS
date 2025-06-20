<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangmasukResource\Pages;
use App\Models\Barang;
use App\Models\Barangmasuk;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as ActionsAction;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Card;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
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

    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-circle';

    protected static ?string $navigationGroup = 'Flow Barang';

    protected static ?string $navigationLabel = 'Barang Masuk';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'flow/barang-masuk';

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

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah total barang masuk yang tercatat';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Informasi Transaksi Section
                Forms\Components\Section::make()
                    ->heading('Informasi Transaksi')
                    ->description('Detail informasi transaksi barang masuk')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->collapsed(false)
                    ->aside()
                    ->compact()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(['sm' => 1, 'md' => 2])
                            ->schema([
                                // Group Date and Status
                                Forms\Components\Fieldset::make('Detail Waktu & Status')
                                    ->schema([
                                        Forms\Components\DatePicker::make('tanggal_barang_masuk')
                                            ->label('Tanggal Masuk')
                                            ->default(now())
                                            ->required()
                                            ->disabled()
                                            ->dehydrated(true)
                                            ->prefixIcon('heroicon-o-calendar')
                                            ->displayFormat('d F Y')
                                            ->extraAttributes(['style' => 'font-weight: 500;']),

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
                                    ]),

                                // Group People Information
                                Forms\Components\Fieldset::make('Informasi Penanggung Jawab')
                                    ->schema([
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
                                            ->disabled()
                                            ->dehydrated(true)
                                            ->searchable()
                                            ->preload()
                                            ->prefixIcon('heroicon-o-user'),
                                    ]),
                            ]),

                        // Conditional Project Name
                        Forms\Components\TextInput::make('project_name')
                            ->label('Nama Project')
                            ->placeholder('Masukkan nama project')
                            ->required(fn(Get $get) => $get('status') === 'project')
                            ->visible(fn(Get $get) => $get('status') === 'project')
                            ->prefixIcon('heroicon-o-briefcase')
                            ->columnSpanFull(),
                    ]),

                // Detail Barang Section
                Forms\Components\Section::make()
                    ->heading('Detail Barang')
                    ->description('Informasi detail barang yang masuk ke inventori')
                    ->icon('heroicon-o-cube')
                    ->collapsible()
                    ->collapsed(false)
                    ->aside()
                    ->compact()
                    ->schema([
                        // Transaction Type Selection
                        Forms\Components\Card::make()
                            ->schema([
                                Forms\Components\Radio::make('tipe_transaksi')
                                    ->label('Pilihan Jenis Input Barang')
                                    ->options([
                                        'barang_lama' => 'Pilih Barang yang Sudah Ada',
                                        'barang_baru' => 'Tambah Barang Baru',
                                    ])
                                    ->default('barang_lama')
                                    ->required()
                                    ->inline()
                                    ->live()
                                    ->reactive()
                                    ->helperText('Pilih jenis input sesuai kebutuhan transaksi barang masuk')
                                    ->columnSpanFull(),
                            ])
                            ->extraAttributes(['class' => 'border-2 border-dashed border-gray-300 bg-gray-50/50 dark:border-gray-600 dark:bg-gray-800/50']),

                        // Repeater for Existing Items
                        Forms\Components\Repeater::make('barang_lama_items')
                            ->label('Barang Yang Sudah Ada')
                            ->schema([
                                Forms\Components\Grid::make(['sm' => 1, 'md' => 3])
                                    ->schema([
                                        Forms\Components\Select::make('barang_id')
                                            ->label('Pilih Barang')
                                            ->relationship('barang', 'nama_barang')
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->live()
                                            ->required()
                                            ->placeholder('Cari atau pilih barang...')
                                            ->prefixIcon('heroicon-o-magnifying-glass')
                                            ->columnSpan(1)
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state) {
                                                    $barang = \App\Models\Barang::find($state);
                                                    if ($barang) {
                                                        $set('kode_barang', $barang->kode_barang);
                                                        $set('stok_saat_ini', $barang->jumlah_barang);
                                                    }
                                                }
                                            }),

                                        Forms\Components\TextInput::make('stok_saat_ini')
                                            ->label('Stok Saat Ini')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-o-archive-box')
                                            ->placeholder('0')
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('jumlah_barang_masuk')
                                            ->label('Jumlah Barang Masuk')
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->live()
                                            ->minValue(1)
                                            ->prefixIcon('heroicon-o-plus')
                                            ->placeholder('0')
                                            ->columnSpan(1),

                                        Forms\Components\Hidden::make('kode_barang'),
                                    ])
                            ])
                            ->itemLabel(
                                fn(array $state): ?string =>
                                isset($state['barang_id']) ?
                                    Barang::find($state['barang_id'])?->nama_barang . ' (' . ($state['jumlah_barang_masuk'] ?? '0') . ' unit)' :
                                    'Barang Yang Sudah Ada'
                            )
                            ->collapsible()
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah')
                            ->reorderableWithButtons()
                            ->deleteAction(
                                fn(ActionsAction $action) => $action
                            )
                            ->visible(fn(Get $get) => $get('tipe_transaksi') === 'barang_lama'),

                        // Repeater for New Items
                        Forms\Components\Repeater::make('barang_baru_items')
                            ->label('Barang Baru')
                            ->schema([
                                Forms\Components\Grid::make(['sm' => 1, 'md' => 2])
                                    ->schema([
                                        Forms\Components\TextInput::make('serial_number')
                                            ->label('Serial Number')
                                            ->required()
                                            ->unique(table: 'barangs', column: 'serial_number', ignoreRecord: true)
                                            ->dehydrated()
                                            ->prefixIcon('heroicon-o-identification')
                                            ->extraAttributes(['style' => 'font-family: monospace;']),

                                        Forms\Components\TextInput::make('kode_barang')
                                            ->label('Kode Barang')
                                            ->numeric()
                                            ->unique(table: 'barangs', column: 'kode_barang', ignoreRecord: true)
                                            ->required()
                                            ->dehydrated()
                                            ->prefixIcon('heroicon-o-hashtag')
                                            ->extraAttributes(['style' => 'font-family: monospace;']),

                                        Forms\Components\TextInput::make('nama_barang')
                                            ->label('Nama Barang')
                                            ->required()
                                            ->placeholder('Masukkan nama barang...')
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
                                                    ->label('Nama Kategori')
                                                    ->placeholder('Masukkan nama kategori baru...'),
                                            ])
                                            ->required()
                                            ->prefixIcon('heroicon-o-tag'),

                                        Forms\Components\TextInput::make('jumlah_barang_masuk')
                                            ->label('Jumlah Barang Masuk')
                                            ->numeric()
                                            ->required()
                                            ->prefixIcon('heroicon-o-plus')
                                            ->prefix('Qty:')
                                            ->extraAttributes(['class' => 'font-mono text-center'])
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->itemLabel(
                                fn(array $state): ?string =>
                                isset($state['nama_barang']) ?
                                    $state['nama_barang'] . ' (' . ($state['jumlah_barang_masuk'] ?? '0') . ' unit)' :
                                    'Barang Baru'
                            )
                            ->collapsible()
                            ->defaultItems(1)
                            ->reactive()
                            ->live()
                            ->addActionLabel('+ Tambah')
                            ->reorderableWithButtons()
                            ->deleteAction(
                                fn(ActionsAction $action) => $action
                            )
                            ->visible(fn(Get $get) => $get('tipe_transaksi') === 'barang_baru'),
                    ]),
            ])
            ->live()
            ->reactive();
    }


    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultGroup('status')
            ->columns([
                TextColumn::make('barang.serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Serial number berhasil disalin!')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-o-identification')
                    ->weight('bold')
                    ->toggleable(),

                TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    
                    ->wrap()
                    ->icon('heroicon-o-cube')
                    ->toggleable(),

                TextColumn::make('barang.kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->color('gray')
                    ->icon('heroicon-o-qr-code')
                    ->toggleable(),

                TextColumn::make('jumlah_barang_masuk')
                    ->label('Jumlah Masuk')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-o-arrow-trending-up')
                    ->alignCenter()
                    ->weight('bold')
                    ->toggleable(),

                TextColumn::make('tanggal_barang_masuk')
                    ->label('Tanggal Masuk')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('dibeli')
                    ->label('Diajukan Oleh')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->icon('heroicon-o-user')
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
                    ->icon(fn(string $state): string => match ($state) {
                        'oprasional_kantor' => 'heroicon-o-building-office',
                        'project' => 'heroicon-o-briefcase',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'oprasional_kantor' => 'Operasional Kantor',
                        'project' => 'Project',
                        default => ucfirst($state),
                    })
                    ->toggleable(),

                TextColumn::make('project_name')
                    ->label('Nama Project')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak ada project')
                    ->limit(25)
                    ->tooltip(fn($record) => $record->project_name)
                    ->icon('heroicon-o-document-text')
                    ->toggleable()
                    ->visible(fn($livewire) => $livewire->activeTab === 'project'),

                TextColumn::make('user.name')
                    ->label('Diinput Oleh')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->icon('heroicon-o-user-circle')
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->since() // Menampilkan dalam format "x hari yang lalu"
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->since() // Menampilkan dalam format "x jam yang lalu"
                    ->sortable()
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->searchable()
            ->filters([
                TrashedFilter::make()
                ->searchable()
                ->preload()
                ->trueLabel('Data Terhapus')
                ->falseLabel('Data Akif')
                ->label('Dihapus'),
                SelectFilter::make('status')
                    ->label('Status Penggunaan')
                    ->options([
                        'oprasional_kantor' => 'Operasional Kantor',
                        'project' => 'Project',
                    ])  
                    ->searchable()
                    ->preload(),

                Filter::make('tanggal_barang_masuk')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->native(false),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false),
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
                Tables\Actions\ViewAction::make(),
                ActionGroup::make([
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
                ])
                    ->label('Tindakan')
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->color('danger')
                    ->tooltip('Aksi untuk barang masuk yang dipilih'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header Section dengan Informasi Utama
                Section::make()
                    ->heading('Ringkasan Transaksi')
                    ->description('Informasi utama transaksi barang masuk')
                    ->icon('heroicon-o-document-text')
                    ->aside()
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 2, 'lg' => 3])
                            ->schema([
                                TextEntry::make('tanggal_barang_masuk')
                                    ->label('Tanggal Masuk')
                                    ->date('d F Y')
                                    ->icon('heroicon-o-calendar')
                                    ->weight('bold')
                                    ->extraAttributes(['class' => 'text-lg']),

                                TextEntry::make('status')
                                    ->label('Status Penggunaan')
                                    ->badge()
                                    ->icon(fn(string $state): string => match ($state) {
                                        'oprasional_kantor' => 'heroicon-o-building-office',
                                        'project' => 'heroicon-o-briefcase',
                                        default => 'heroicon-o-question-mark-circle',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'oprasional_kantor' => 'Operasional Kantor',
                                        'project' => 'Project',
                                        default => ucfirst($state),
                                    }),

                                TextEntry::make('jumlah_barang_masuk')
                                    ->label('Total Barang Masuk')
                                    ->numeric()
                                    ->icon('heroicon-o-arrow-trending-up')
                                    ->weight('bold')
                                    ->suffix(' unit')
                                    ->extraAttributes(['class' => 'text-lg']),
                            ]),

                        // Project Name (Conditional)
                        TextEntry::make('project_name')
                            ->label('Nama Project')
                            ->placeholder('Tidak ada project')
                            ->icon('heroicon-o-briefcase')
                            ->color('info')
                            ->weight('semibold')
                            ->visible(fn($record) => $record->status === 'project' && !empty($record->project_name))
                            ->columnSpanFull(),
                    ]),

                // Informasi Penanggung Jawab
                Section::make()
                    ->heading('Informasi Penanggung Jawab')
                    ->description('Detail penanggung jawab dan yang menginput data')
                    ->icon('heroicon-o-users')
                    ->aside()
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 2])
                            ->schema([
                                TextEntry::make('dibeli')
                                    ->label('Diajukan Oleh')
                                    ->formatStateUsing(fn($state) => ucwords($state))
                                    ->icon('heroicon-o-user')
                                    ->weight('semibold'),

                                TextEntry::make('user.name')
                                    ->label('Yang Menginput')
                                    ->formatStateUsing(fn($state) => ucwords($state))
                                    ->icon('heroicon-o-user-circle')
                                    ->weight('semibold'),
                            ]),
                    ]),

                // Detail Barang Section
                Section::make()
                    ->heading('Detail Barang')
                    ->description('Informasi lengkap barang yang masuk ke inventori')
                    ->icon('heroicon-o-cube')
                    ->aside()
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 2, 'lg' => 3])
                            ->schema([
                                TextEntry::make('barang.serial_number')
                                    ->label('Serial Number')
                                    ->copyable()
                                    ->copyMessage('Serial number berhasil disalin!')
                                    ->copyMessageDuration(1500)
                                    ->icon('heroicon-o-identification')
                                    ->weight('bold')
                                    ->extraAttributes(['class' => 'font-mono text-sm']),

                                TextEntry::make('barang.kode_barang')
                                    ->label('Kode Barang')
                                    ->icon('heroicon-o-qr-code')
                                    ->extraAttributes(['class' => 'font-mono']),

                                TextEntry::make('barang.nama_barang')
                                    ->label('Nama Barang')
                                    ->icon('heroicon-o-tag')
                                    ->weight('semibold'),
                            ]),

                        // Kategori Barang (jika ada)
                        TextEntry::make('barang.kategori.nama_kategori')
                            ->label('Kategori Barang')
                            ->icon('heroicon-o-tag')
                            ->placeholder('Belum dikategorikan')
                            ->columnSpanFull(),
                    ]),

                // Statistik dan Informasi Tambahan
                Section::make()
                    ->heading('Informasi Sistem')
                    ->description('Data tambahan dan riwayat perubahan')
                    ->icon('heroicon-o-information-circle')
                    ->aside()
                    ->collapsible()
                    ->collapsed(true)
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 2])
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d M Y, H:i')
                                    ->since()
                                    ->icon('heroicon-o-clock')
                                    ->color('gray'),

                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->dateTime('d M Y, H:i')
                                    ->since()
                                    ->icon('heroicon-o-arrow-path')
                                    ->color('gray'),
                            ]),
                    ]),

                // Summary Card dengan Statistik
                Card::make([
                    Grid::make(['sm' => 1, 'md' => 3])
                        ->schema([
                            TextEntry::make('barang.jumlah_barang')
                                ->label('Stok Saat Ini')
                                ->numeric()
                                ->suffix(' unit')
                                ->icon('heroicon-o-archive-box')
                                ->weight('bold'),

                            TextEntry::make('jumlah_barang_masuk')
                                ->label('Barang Masuk')
                                ->numeric()
                                ->suffix(' unit')
                                ->icon('heroicon-o-plus-circle')
                                ->weight('bold'),

                            TextEntry::make('calculated_previous_stock')
                                ->label('Stok Sebelumnya')
                                ->state(fn($record) => $record->barang->jumlah_barang - $record->jumlah_barang_masuk)
                                ->numeric()
                                ->suffix(' unit')
                                ->icon('heroicon-o-minus-circle')
                                ->weight('bold'),
                        ]),
                ])
                    ->extraAttributes([
                        'class' => 'bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-dashed border-blue-200 dark:border-blue-700'
                    ]),
            ])
            ->columns(['sm' => 1, 'lg' => 2]);
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
