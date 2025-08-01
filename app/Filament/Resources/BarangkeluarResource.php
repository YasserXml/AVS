<?php

namespace App\Filament\Resources;

use App\Exports\BarangKeluarExport;
use App\Filament\Resources\BarangkeluarResource\Pages;
use App\Models\Barang;
use App\Models\Barangkeluar;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BarangkeluarResource extends Resource
{
    protected static ?string $model = Barangkeluar::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-circle';

    protected static ?string $activeNavigationIcon = 'heroicon-s-arrow-right-circle';

    protected static ?string $navigationLabel = 'Barang Keluar';

    protected static ?string $navigationGroup = 'Flow Barang';

    protected static ?string $modelLabel = 'Barang Keluar';

    protected static ?string $pluralModelLabel = 'Barang Keluar';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'flow/barang-keluar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->heading('Informasi Transaksi Barang Keluar')
                    ->description('Detail informasi barang yang akan dikeluarkan dari inventori')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->collapsible()
                    ->collapsed(false)
                    ->compact()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(['sm' => 1, 'md' => 2])
                            ->schema([
                                // Group Date and Status
                                Forms\Components\Fieldset::make('Detail Waktu & Status')
                                    ->schema([
                                        Forms\Components\DatePicker::make('tanggal_keluar_barang')
                                            ->label('Tanggal Keluar')
                                            ->default(now())
                                            ->required()
                                            ->live()
                                            ->disabled()
                                            ->dehydrated(true)
                                            ->native(false)
                                            ->reactive()
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

                                        Forms\Components\TextInput::make('project_name')
                                            ->label('Nama Project')
                                            ->placeholder('Masukkan nama project')
                                            ->reactive()
                                            ->live()
                                            ->required(fn(Get $get) => $get('status') === 'project')
                                            ->visible(fn(Get $get) => $get('status') === 'project')
                                            ->prefixIcon('heroicon-o-briefcase'),
                                    ]),
                            ]),
                    ]),

                Forms\Components\Section::make()
                    ->heading('Detail Barang Keluar')
                    ->description('Pilih barang yang akan dikeluarkan dari inventori berdasarkan subkategori')
                    ->icon('heroicon-o-cube-transparent')
                    ->collapsible()
                    ->collapsed(false)
                    ->compact()
                    ->schema([
                        Forms\Components\Repeater::make('barang_items')
                            ->label('Daftar Barang Keluar')
                            ->reactive()
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->columns(['default' => 1, 'sm' => 2, 'md' => 3, 'lg' => 6])
                                    ->schema([
                                        // Baris 1: Kategori dan Subkategori
                                        Forms\Components\Select::make('kategori_id')
                                            ->label('Pilih Kategori')
                                            ->relationship('kategori', 'nama_kategori')
                                            ->searchable()
                                            ->reactive()
                                            ->live()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Pilih kategori...')
                                            ->prefixIcon('heroicon-o-tag')
                                            ->columnSpan(['default' => 1, 'sm' => 1, 'md' => 2, 'lg' => 2])
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                // Reset semua pilihan setelah kategori
                                                $set('subkategori_id', null);
                                                $set('barang_id', null);
                                                $fieldsToReset = [
                                                    'total_stok_subkategori',
                                                    'stok_tersedia',
                                                    'kode_barang_display',
                                                    'serial_number_display',
                                                    'kategori_display',
                                                    'nama_barang_display',
                                                    'merek_display',
                                                    'model_display',
                                                    'garansi_display',
                                                    'processor_display',
                                                    'ram_display',
                                                    'storage_display',
                                                    'jumlah_barang_keluar',
                                                    'sisa_stok_display'
                                                ];
                                                foreach ($fieldsToReset as $field) {
                                                    $set($field, null);
                                                }
                                            }),

                                        Forms\Components\Select::make('subkategori_id')
                                            ->label('Pilih Subkategori')
                                            ->options(function (Get $get) {
                                                $kategoriId = $get('kategori_id');
                                                if (!$kategoriId) {
                                                    return [];
                                                }

                                                return \App\Models\Subkategori::where('kategori_id', $kategoriId)
                                                    ->with(['barang' => function ($query) {
                                                        $query->selectRaw('subkategori_id, SUM(jumlah_barang) as total_stok')
                                                            ->groupBy('subkategori_id');
                                                    }])
                                                    ->get()
                                                    ->mapWithKeys(function ($subkategori) {
                                                        $totalStok = \App\Models\Barang::where('subkategori_id', $subkategori->id)
                                                            ->sum('jumlah_barang');

                                                        return [
                                                            $subkategori->id => "{$subkategori->nama_subkategori} (Total Stok: {$totalStok} unit)"
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->reactive()
                                            ->live()
                                            ->required()
                                            ->placeholder('Pilih subkategori...')
                                            ->prefixIcon('heroicon-o-squares-2x2')
                                            ->columnSpan(['default' => 1, 'sm' => 1, 'md' => 2, 'lg' => 2])
                                            ->disabled(fn(Get $get) => !$get('kategori_id'))
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state) {
                                                    // Hitung total stok subkategori
                                                    $totalStokSubkategori = \App\Models\Barang::where('subkategori_id', $state)
                                                        ->sum('jumlah_barang');
                                                    $set('total_stok_subkategori', $totalStokSubkategori);

                                                    // Get nama subkategori untuk display
                                                    $subkategori = \App\Models\Subkategori::find($state);
                                                    if ($subkategori) {
                                                        $set('subkategori_display', $subkategori->nama_subkategori);
                                                    }
                                                }

                                                // Reset pilihan barang setelah subkategori berubah
                                                $set('barang_id', null);
                                                $fieldsToReset = [
                                                    'stok_tersedia',
                                                    'kode_barang_display',
                                                    'serial_number_display',
                                                    'nama_barang_display',
                                                    'merek_display',
                                                    'model_display',
                                                    'garansi_display',
                                                    'processor_display',
                                                    'ram_display',
                                                    'storage_display',
                                                    'jumlah_barang_keluar',
                                                    'sisa_stok_display'
                                                ];
                                                foreach ($fieldsToReset as $field) {
                                                    $set($field, null);
                                                }
                                            }),

                                        // Total Stok Subkategori
                                        Forms\Components\TextInput::make('total_stok_subkategori')
                                            ->label('Total Stok Subkategori')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-o-archive-box-arrow-down')
                                            ->placeholder('0')
                                            ->columnSpan(['default' => 1, 'sm' => 2, 'md' => 1, 'lg' => 1])
                                            ->extraAttributes(['class' => 'text-blue-600 font-bold'])
                                            ->suffix(' unit'),

                                        // Stok Item
                                        Forms\Components\TextInput::make('stok_tersedia')
                                            ->label('Stok Item')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-o-archive-box')
                                            ->placeholder('0')
                                            ->columnSpan(['default' => 1, 'sm' => 2, 'md' => 1, 'lg' => 1])
                                            ->extraAttributes(['class' => 'text-green-600 font-semibold'])
                                            ->suffix(' unit'),
                                    ]),

                                // Baris 2: Serial Number dan Jumlah Keluar
                                Forms\Components\Grid::make()
                                    ->columns(['default' => 1, 'sm' => 1, 'md' => 2, 'lg' => 2])
                                    ->schema([
                                        Forms\Components\Select::make('barang_id')
                                            ->label('Pilih Serial Number')
                                            ->options(function (Get $get) {
                                                $subkategoriId = $get('subkategori_id');
                                                if (!$subkategoriId) {
                                                    return [];
                                                }

                                                return \App\Models\Barang::where('subkategori_id', $subkategoriId)
                                                    ->where('jumlah_barang', '>', 0)
                                                    ->get()
                                                    ->mapWithKeys(function ($barang) {
                                                        return [
                                                            $barang->id => "{$barang->serial_number} - {$barang->nama_barang} (Stok: {$barang->jumlah_barang})"
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->reactive()
                                            ->live()
                                            ->required()
                                            ->placeholder('Pilih serial number...')
                                            ->prefixIcon('heroicon-o-hashtag')
                                            ->columnSpan(1)
                                            ->disabled(fn(Get $get) => !$get('subkategori_id'))
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state) {
                                                    $barang = \App\Models\Barang::with(['kategori', 'subkategori'])->find($state);
                                                    if ($barang) {
                                                        // Set data dasar barang
                                                        $set('stok_tersedia', $barang->jumlah_barang);
                                                        $set('kode_barang_display', $barang->kode_barang);
                                                        $set('serial_number_display', $barang->serial_number);
                                                        $set('nama_barang_display', $barang->nama_barang);
                                                        $set('kategori_display', $barang->kategori?->nama_kategori ?? '-');

                                                        // Set spesifikasi barang dari JSON
                                                        $spesifikasi = $barang->spesifikasi ?? [];

                                                        // Untuk elektronik
                                                        $set('merek_display', $spesifikasi['spec_brand'] ?? $spesifikasi['merek'] ?? '-');
                                                        $set('model_display', $spesifikasi['spec_model'] ?? $spesifikasi['model'] ?? '-');
                                                        $set('garansi_display', $spesifikasi['spec_garansi'] ?? $spesifikasi['garansi'] ?? '-');

                                                        // Untuk komputer - tampilkan spec yang paling penting
                                                        if (isset($spesifikasi['spec_processor'])) {
                                                            $set('processor_display', $spesifikasi['spec_processor']);
                                                            $set('ram_display', $spesifikasi['spec_ram'] ?? '-');
                                                            $set('storage_display', $spesifikasi['spec_storage'] ?? '-');
                                                        }

                                                        // Reset jumlah keluar
                                                        $set('jumlah_barang_keluar', 1);

                                                        // Notifikasi stok rendah
                                                        if ($barang->jumlah_barang <= 5) {
                                                            Notification::make()
                                                                ->title('Peringatan Stok Rendah!')
                                                                ->body("Stok {$barang->nama_barang} dengan SN: {$barang->serial_number} tinggal {$barang->jumlah_barang} unit")
                                                                ->warning()
                                                                ->persistent()
                                                                ->send();
                                                        }
                                                    }
                                                } else {
                                                    // Reset semua field jika tidak ada barang dipilih
                                                    $fieldsToReset = [
                                                        'stok_tersedia',
                                                        'kode_barang_display',
                                                        'serial_number_display',
                                                        'nama_barang_display',
                                                        'kategori_display',
                                                        'merek_display',
                                                        'model_display',
                                                        'garansi_display',
                                                        'processor_display',
                                                        'ram_display',
                                                        'storage_display',
                                                        'jumlah_barang_keluar',
                                                        'sisa_stok_display'
                                                    ];
                                                    foreach ($fieldsToReset as $field) {
                                                        $set($field, null);
                                                    }
                                                }
                                            }),

                                        Forms\Components\TextInput::make('jumlah_barang_keluar')
                                            ->label('Jumlah Keluar')
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->live()
                                            ->minValue(1)
                                            ->prefixIcon('heroicon-o-minus')
                                            ->placeholder('1')
                                            ->columnSpan(1)
                                            ->suffix(' unit')
                                            ->rules([
                                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                                    $stokTersedia = $get('stok_tersedia');
                                                    if ($value && $stokTersedia && $value > $stokTersedia) {
                                                        $fail("Jumlah keluar tidak boleh melebihi stok tersedia ({$stokTersedia} unit)");
                                                    }
                                                },
                                            ])
                                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                $stokTersedia = $get('stok_tersedia');
                                                if ($state && $stokTersedia) {
                                                    if ($state > $stokTersedia) {
                                                        Notification::make()
                                                            ->title('Jumlah Melebihi Stok!')
                                                            ->body("Stok tersedia hanya {$stokTersedia} unit")
                                                            ->danger()
                                                            ->send();
                                                    } else {
                                                        $sisaStok = $stokTersedia - $state;
                                                        $set('sisa_stok_display', $sisaStok);

                                                        if ($sisaStok <= 3) {
                                                            Notification::make()
                                                                ->title('Peringatan Stok!')
                                                                ->body("Setelah transaksi ini, sisa stok akan menjadi {$sisaStok} unit")
                                                                ->warning()
                                                                ->send();
                                                        }
                                                    }
                                                }
                                            }),
                                    ]),

                                // Hidden fields untuk menyimpan data
                                Forms\Components\Hidden::make('nama_barang_display'),
                                Forms\Components\Hidden::make('kode_barang_display'),
                                Forms\Components\Hidden::make('serial_number_display'),
                                Forms\Components\Hidden::make('kategori_display'),
                                Forms\Components\Hidden::make('subkategori_display'),
                                Forms\Components\Hidden::make('merek_display'),
                                Forms\Components\Hidden::make('model_display'),
                                Forms\Components\Hidden::make('garansi_display'),
                                Forms\Components\Hidden::make('processor_display'),
                                Forms\Components\Hidden::make('ram_display'),
                                Forms\Components\Hidden::make('storage_display'),
                                Forms\Components\Hidden::make('total_stok_subkategori'),
                            ])
                            ->itemLabel(
                                fn(array $state): ?string =>
                                isset($state['subkategori_display']) && isset($state['total_stok_subkategori']) ?
                                    "{$state['subkategori_display']} - Total Stok: {$state['total_stok_subkategori']} unit" .
                                    (isset($state['serial_number_display']) ? " | {$state['nama_barang_display']} (SN: {$state['serial_number_display']}) - {$state['jumlah_barang_keluar']} unit" : '') :
                                    'Barang Keluar'
                            )
                            ->collapsible()
                            ->collapsed(fn(array $state): bool => !empty($state['barang_id']))
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Barang Keluar')
                            ->reorderableWithButtons()
                            ->deleteAction(
                                fn(Forms\Components\Actions\Action $action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('Hapus Barang')
                                    ->modalDescription('Apakah Anda yakin ingin menghapus barang ini dari daftar?')
                                    ->modalSubmitActionLabel('Ya, Hapus')
                            )
                            ->cloneAction(
                                fn(Forms\Components\Actions\Action $action) => $action
                                    ->label('Duplikat')
                                    ->icon('heroicon-o-document-duplicate')
                            )
                            ->maxItems(10)
                            ->minItems(1),
                    ]),
            ])
            ->live()
            ->reactive();
    }
    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_keluar_barang')
                    ->label('Tanggal Keluar')
                    ->date('d F Y')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn($record) => 'SN: ' . ($record->barang->serial_number ?? '-') . ' | Kode: ' . ($record->barang->kode_barang ?? '-'))
                    ->icon('heroicon-o-cube'),

                Tables\Columns\TextColumn::make('barang.kategori.nama_kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag'),

                Tables\Columns\TextColumn::make('barang.subkategori.nama_subkategori')
                    ->label('Subkategori')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-squares-2x2')
                    ->description(function ($record) {
                        if ($record->barang && $record->barang->subkategori) {
                            // Hitung total stok subkategori saat ini
                            $totalStok = \App\Models\Barang::where('subkategori_id', $record->barang->subkategori_id)
                                ->sum('jumlah_barang');
                            return "Total stok: {$totalStok} unit";
                        }
                        return null;
                    }),

                Tables\Columns\TextColumn::make('jumlah_barang_keluar')
                    ->label('Jumlah Keluar')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->weight(FontWeight::Bold)
                    ->color('danger')
                    ->suffix(' unit')
                    ->icon('heroicon-o-minus-circle'),

                Tables\Columns\TextColumn::make('barang.jumlah_barang')
                    ->label('Sisa Stok Item')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->weight(FontWeight::Medium)
                    ->color(fn($record) => $record->barang && $record->barang->jumlah_barang <= 5 ? 'danger' : ($record->barang && $record->barang->jumlah_barang <= 10 ? 'warning' : 'success'))
                    ->suffix(' unit')
                    ->icon(fn($record) => $record->barang && $record->barang->jumlah_barang <= 5 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-archive-box')
                    ->description('Per serial number'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'oprasional_kantor',
                        'warning' => 'project',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'oprasional_kantor' => 'Operasional Kantor',
                        'project' => 'Project',
                        default => $state,
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'oprasional_kantor' => 'heroicon-o-building-office',
                        'project' => 'heroicon-o-briefcase',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('project_name')
                    ->label('Nama Project')
                    ->searchable()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->placeholder('Bukan dari project')
                    ->description('Khusus untuk status project')
                    ->icon('heroicon-o-folder'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Diinput Oleh')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray')
                    ->icon('heroicon-o-clock'),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Sampah')
                    ->indicator('Terhapus')
                    ->trueLabel('Data aktif + terhapus')
                    ->falseLabel('Terhapus')
                    ->searchable()
                    ->preload(),
                // Filter berdasarkan Status
                SelectFilter::make('status')
                    ->label('Status Penggunaan')
                    ->options([
                        'oprasional_kantor' => 'Operasional Kantor',
                        'project' => 'Project',
                    ])
                    ->placeholder('Semua Status')
                    ->multiple(),

                // Filter berdasarkan Kategori
                SelectFilter::make('kategori')
                    ->label('Kategori Barang')
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                // Filter berdasarkan tanggal
                Filter::make('tanggal_keluar')
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
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_keluar_barang', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_keluar_barang', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Dari: ' . Carbon::parse($data['dari_tanggal'])->format('d M Y');
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Sampai: ' . Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Detail Barang Keluar')
                    ->modalWidth('3xl'),

                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Edit Barang Keluar')
                        ->modalWidth('4xl')
                        ->color('info'),

                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Hapus Barang Keluar')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data barang keluar ini? Tindakan ini tidak dapat dibatalkan.')
                        ->before(function ($record) {
                            // Kembalikan stok barang
                            $record->barang->increment('jumlah_barang', $record->jumlah_barang_keluar);

                            Notification::make()
                                ->title('Stok Dikembalikan')
                                ->body("Stok {$record->barang->nama_barang} dikembalikan sebanyak {$record->jumlah_barang_keluar} unit")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('exportAll')
                    ->label('Export Semua Data')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $exporter = new BarangKeluarExport();
                        return $exporter->export();
                    })
                    ->visible(
                        fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])
                    ),
            ])
            ->bulkActions([
                BulkAction::make('exportSelected')
                    ->label('Export Data Terpilih')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function (Collection $records) {
                        $exporter = new BarangKeluarExport();
                        return $exporter->export($records);
                    })
                    ->visible(
                        fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Export Data Barang Keluar Terpilih')
                    ->modalDescription('Apakah Anda yakin ingin mengexport data barang keluar yang dipilih?')
                    ->modalSubmitActionLabel('Ya, Export'),

                Tables\Actions\DeleteBulkAction::make()
                    ->modalHeading('Hapus Beberapa Data')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data barang keluar yang dipilih? Stok akan dikembalikan otomatis.')
                    ->before(function ($records) {
                        // Kembalikan stok untuk semua record yang akan dihapus
                        foreach ($records as $record) {
                            $record->barang->increment('jumlah_barang', $record->jumlah_barang_keluar);
                        }

                        Notification::make()
                            ->title('Stok Dikembalikan')
                            ->body('Stok barang telah dikembalikan untuk semua data yang dihapus')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->modalHeading('Hapus Permanen')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data barang keluar ini secara permanen? Tindakan ini tidak dapat dibatalkan.')
                    ->requiresConfirmation()
                    ->color('danger'),
                Tables\Actions\RestoreBulkAction::make()
                    ->modalHeading('Pulihkan Data')
                    ->modalDescription('Apakah Anda yakin ingin memulihkan data barang keluar yang dipilih?')
                    ->requiresConfirmation()
                    ->color('success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->poll('10s') // Auto refresh setiap 10 detik untuk real-time updates
            ->emptyStateHeading('Belum Ada Data Barang Keluar')
            ->emptyStateDescription('Belum ada barang keluar yang tercatat dalam sistem.')
            ->emptyStateIcon('heroicon-o-cube-transparent')
            ->recordUrl(null) // Disable row click navigation
            ->recordAction(null); // Disable default row action
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
            'index' => Pages\ListBarangkeluars::route('/'),
            'create' => Pages\CreateBarangkeluar::route('/create'),
            'edit' => Pages\EditBarangkeluar::route('/{record}/edit'),
        ];
    }
}
