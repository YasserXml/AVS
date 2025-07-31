<?php

namespace App\Filament\Resources;

use App\Exports\BarangMasukExport;
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
use Filament\Tables\Actions\Action as TablesActionsAction;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class BarangmasukResource extends Resource
{
    protected static ?string $model = Barangmasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-circle';

    protected static ?string $activeNavigationIcon = 'heroicon-s-arrow-left-circle';

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
                Forms\Components\Section::make()
                    ->heading('Informasi Transaksi')
                    ->description('Detail informasi transaksi barang masuk')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->collapsed(false)
                    
                    ->compact()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(['sm' => 1, 'md' => 2])
                            ->schema([
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
                                            ->live()
                                            ->searchable()
                                            ->prefixIcon('heroicon-o-flag')
                                            ->afterStateUpdated(fn(Set $set) => $set('project_name', null)),
                                    ]),

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

                // Detail Barang Section - Hanya untuk barang baru
                Forms\Components\Section::make()
                    ->heading('Detail Barang Baru')
                    ->description('Masukkan informasi barang baru yang akan ditambahkan ke inventori')
                    ->icon('heroicon-o-cube')
                    ->collapsible()
                    ->collapsed(false)
                    
                    ->compact()
                    ->schema([
                        Forms\Components\Section::make('Identitas Barang')
                            ->description('Masukkan informasi identitas barang')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\TextInput::make('serial_number')
                                    ->label('Nomor Serial')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(table: 'barangs', column: 'serial_number', ignoreRecord: true)
                                    ->placeholder('Masukkan nomor serial barang')
                                    ->prefixIcon('heroicon-m-hashtag')
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('kode_barang')
                                    ->label('Kode Barang')
                                    ->required()
                                    ->numeric()
                                    ->unique(table: 'barangs', column: 'kode_barang', ignoreRecord: true)
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
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        // Auto capitalize first letter of each word
                                        $set('nama_barang', ucwords(strtolower($state)));
                                    }),
                            ])
                            ->columns(['default' => 1, 'md' => 2])
                            ->collapsible()
                            ->persistCollapsed(),

                        Forms\Components\Section::make('Detail Barang')
                            ->description('Masukkan detail informasi barang')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('jumlah_barang_masuk')
                                    ->label('Jumlah Barang Masuk')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Masukkan jumlah barang')
                                    ->prefixIcon('heroicon-o-plus')
                                    ->suffix('unit')
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
                                        $set('spec_processor', null);
                                        $set('spec_ram', null);
                                        $set('spec_storage', null);
                                        $set('spec_vga', null);
                                        $set('spec_motherboard', null);
                                        $set('spec_psu', null);
                                        $set('spec_brand', null);
                                        $set('spec_model', null);
                                        $set('spec_garansi', null);
                                    }),
                            ])
                            ->columns(['default' => 1, 'md' => 2])
                            ->collapsible()
                            ->persistCollapsed(),

                        // Section Spesifikasi
                        Forms\Components\Section::make('Spesifikasi Barang')
                            ->description('Masukkan spesifikasi detail barang')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        // Spesifikasi untuk Komputer
                                        Forms\Components\Group::make()
                                            ->schema([
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
                                                $kategori = \App\Models\Kategori::find($kategoriId);
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
                                                $kategori = \App\Models\Kategori::find($kategoriId);
                                                return $kategori && strtolower($kategori->nama_kategori) === 'elektronik';
                                            }),
                                    ])
                                    ->visible(fn(callable $get) => $get('kategori_id') !== null),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->persistCollapsed()
                            ->live(),
                    ]),
            ])
            ->live();
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

                TextColumn::make('barang.kategori.nama_kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-tag')
                    ->toggleable(),

                TextColumn::make('jumlah_barang_masuk')
                    ->label('Jumlah Barang Masuk')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-o-arrow-trending-up')
                    ->alignCenter()
                    ->weight('bold')
                    ->suffix(' unit')
                    ->color('success')
                    ->toggleable(),

                TextColumn::make('tanggal_barang_masuk')
                    ->label('Tanggal Masuk')
                    ->date('d M Y')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->icon('heroicon-o-calendar')
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
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->placeholder('Tidak ada project')
                    ->limit(25)
                    ->tooltip(fn($record) => $record->project_name)
                    ->icon('heroicon-o-document-text')
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Diinput Oleh')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->icon('heroicon-o-user-circle')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->since()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->since()
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
                    ->label('Data Terhapus')
                    ->trueLabel('Data Terhapus')
                    ->falseLabel('Data Aktif'),

                SelectFilter::make('status')
                    ->label('Status Penggunaan')
                    ->options([
                        'oprasional_kantor' => 'Operasional Kantor',
                        'project' => 'Project',
                    ])
                    ->searchable()
                    ->preload(),

                SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->multiple(),

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

                Filter::make('jumlah_barang_masuk')
                    ->form([
                        Forms\Components\TextInput::make('jumlah_min')
                            ->label('Jumlah Minimum')
                            ->numeric()
                            ->placeholder('0'),
                        Forms\Components\TextInput::make('jumlah_max')
                            ->label('Jumlah Maksimum')
                            ->numeric()
                            ->placeholder('999999'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['jumlah_min'],
                                fn(Builder $query, $jumlah): Builder => $query->where('jumlah_barang_masuk', '>=', $jumlah),
                            )
                            ->when(
                                $data['jumlah_max'],
                                fn(Builder $query, $jumlah): Builder => $query->where('jumlah_barang_masuk', '<=', $jumlah),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['jumlah_min'] ?? null) {
                            $indicators[] = 'Jumlah Min: ' . $data['jumlah_min'];
                        }
                        if ($data['jumlah_max'] ?? null) {
                            $indicators[] = 'Jumlah Max: ' . $data['jumlah_max'];
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
                        ->modalHeading('Hapus Data Barang Masuk')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Stok barang akan dikurangi sesuai jumlah yang dihapus.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                    Tables\Actions\RestoreAction::make()
                        ->color('success')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->modalHeading('Pulihkan Data Barang Masuk')
                        ->modalDescription('Apakah Anda yakin ingin memulihkan data ini?')
                        ->modalSubmitActionLabel('Ya, Pulihkan'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen')
                        ->modalDescription('Data akan dihapus permanen dan tidak dapat dipulihkan!')
                        ->modalSubmitActionLabel('Ya, Hapus Permanen'),
                ]),
            ])
            ->headerActions([
                TablesActionsAction::make('export')
                    ->label('Export Semua Data')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        return (new BarangMasukExport())->export();
                    })
                    ->visible(
                        fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export_selected')
                    ->label('Export Data Terpilih')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Collection $records) {
                        return (new BarangMasukExport())->export($records);
                    })
                    ->visible(
                        fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])
                    ),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data yang dipilih?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen')
                        ->modalDescription('Data akan dihapus permanen dan tidak dapat dipulihkan!'),
                ])
                    ->label('Aksi')
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
