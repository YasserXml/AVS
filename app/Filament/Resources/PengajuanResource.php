<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Filament\Resources\PengajuanResource\RelationManagers\DetailPengajuanRelationManager;
use App\Models\Barang;
use App\Models\Barangkeluar;
use App\Models\Pengajuan;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Pengajuan Barang';

    protected static ?int $navigationSort = 8;

    protected static ?string $pluralLabel = 'Pengajuan';

    protected static ?string $slug = 'pengajuan';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     $query = parent::getEloquentQuery();

    //     // Ambil user yang sedang login
    //     $user = filament()->auth()->user();

    //     // Jika role-nya user, hanya tampilkan pengajuan miliknya
    //     if ($user->hasRole('user')) {
    //         $query->where('user_id', $user->id);
    //     }

    //     return $query;
    // }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pengajuan Barang')
                    ->description('Lengkapi formulir pengajuan barang dengan benar')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible(false)
                    ->schema([
                        Card::make()
                            ->schema([
                                Hidden::make('user_id')
                                    ->default(fn() => Filament::auth()->id()),

                                Forms\Components\DatePicker::make('tanggal_pengajuan')
                                    ->label('Tanggal Pengajuan')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->displayFormat('d F Y')
                                    ->prefixIcon('heroicon-o-calendar'),

                                Forms\Components\Select::make('status_barang')
                                    ->label('Diperuntukan')
                                    ->options([
                                        'oprasional_kantor' => 'Operasional Kantor',
                                        'project' => 'Project',
                                    ])
                                    ->searchable()
                                    ->default('oprasional_kantor')
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('nama_project', null)),

                                Forms\Components\TextInput::make('nama_project')
                                    ->label('Nama Project')
                                    ->placeholder('Masukkan nama project')
                                    ->required(fn(callable $get) => $get('status_barang') === 'project')
                                    ->visible(fn(callable $get) => $get('status_barang') === 'project')
                                    ->prefixIcon('heroicon-o-briefcase')
                                    ->reactive()
                                    ->live()
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan Pengajuan')
                                    ->rows(4)
                                    ->placeholder('Berikan keterangan detail mengapa barang dibutuhkan')
                                    ->columnSpan('full'),
                            ]),

                        Section::make('Daftar Barang yang Diajukan')
                            ->description('Tambahkan satu atau lebih barang yang ingin diajukan')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Forms\Components\Repeater::make('detail_pengajuan')
                                    ->schema([
                                        Forms\Components\Grid::make(['sm' => 1, 'md' => 2])
                                            ->schema([
                                                Forms\Components\Select::make('barang_id')
                                                    ->label('Pilih Barang')
                                                    ->relationship('barang', 'nama_barang')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live()
                                                    ->prefixIcon('heroicon-o-magnifying-glass')
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $barang = \App\Models\Barang::find($state);
                                                            if ($barang) {
                                                                $set('nama_barang', $barang->nama_barang);
                                                                $set('kategori_id', $barang->kategori_id);
                                                                $set('jumlah_barang', $barang->jumlah_barang);
                                                                $set('kode_barang', $barang->kode_barang);
                                                                $set('serial_number', $barang->serial_number);
                                                                $set('kategoris_id', $barang->kategori_id);
                                                            }
                                                        }
                                                    }),

                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('kode_barang')
                                                            ->label('Kode Barang')
                                                            ->disabled()
                                                            ->dehydrated(false)
                                                            ->prefixIcon('heroicon-o-hashtag'),

                                                        Forms\Components\TextInput::make('serial_number')
                                                            ->label('Serial Number')
                                                            ->disabled()
                                                            ->dehydrated(false)
                                                            ->prefixIcon('heroicon-o-identification'),
                                                    ]),

                                                Forms\Components\TextInput::make('nama_barang')
                                                    ->label('Nama Barang')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->prefixIcon('heroicon-o-tag')
                                                    ->columnSpan(1),

                                                Forms\Components\Select::make('kategori_id')
                                                    ->relationship('kategori', 'nama_kategori')
                                                    ->label('Kategori Barang')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->prefixIcon('heroicon-o-folder')
                                                    ->columnSpan(1),

                                                Forms\Components\Hidden::make('kategoris_id'),

                                                Forms\Components\TextInput::make('jumlah_barang')
                                                    ->label('Stok Tersedia')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->prefix('Tersedia:')
                                                    ->prefixIcon('heroicon-o-clipboard-document-check')
                                                    ->columnSpan(1),

                                                //nama field yang benar sesuai DB
                                                Forms\Components\TextInput::make('Jumlah_barang_diajukan')
                                                    ->label('Jumlah Yang Diajukan')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(1)
                                                    ->maxValue(function (callable $get) {
                                                        $stokTersedia = (int) $get('jumlah_barang');
                                                        return $stokTersedia;
                                                    })
                                                    ->validationMessages([
                                                        'max' => 'Jumlah yang diajukan tidak boleh melebihi stok tersedia (:max)'
                                                    ])
                                                    ->helperText('Masukkan jumlah barang yang dibutuhkan')
                                                    ->prefixIcon('heroicon-o-shopping-cart')
                                                    ->reactive(),
                                            ]),
                                    ])
                                    ->itemLabel(
                                        fn(array $state): ?string =>
                                        isset($state['barang_id']) && isset($state['nama_barang']) ?
                                            $state['nama_barang'] . ' (' . ($state['Jumlah_barang_diajukan'] ?? '0') . ' unit)' :
                                            'Barang Yang Diajukan'
                                    )
                                    ->collapsible()
                                    ->defaultItems(1)
                                    ->addActionLabel('+ Tambah Barang')
                                    ->reorderableWithButtons()
                                    ->deleteAction(
                                        fn(Forms\Components\Actions\Action $action) => $action->requiresConfirmation()
                                    )
                                    ->columns(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Stack::make([
                        Split::make([
                            BadgeColumn::make('status')
                                ->colors([
                                    'warning' => 'pending',
                                    'success' => 'approved',
                                    'danger' => 'rejected',
                                ])
                                ->formatStateUsing(function ($state) {
                                    $labels = [
                                        'pending' => 'Menunggu Persetujuan',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                    ];
                                    return $labels[$state] ?? $state;
                                })
                                ->icons([
                                    'heroicon-m-clock' => 'pending',
                                    'heroicon-m-check-circle' => 'approved',
                                    'heroicon-m-x-circle' => 'rejected',
                                ])
                                ->iconPosition('before')
                                ->size('md'),
                        ]),

                        Split::make([
                            TextColumn::make('user.name')
                                ->label('Pemohon')
                                ->formatStateUsing(fn($state) => "👤 " . $state)
                                ->searchable()
                                ->color('gray')
                                ->weight('medium'),

                            TextColumn::make('tanggal_pengajuan')
                                ->label('Tanggal')
                                ->weight('medium')
                                ->formatStateUsing(fn($state) => "📅 " . Carbon::parse($state)->format('d M Y'))
                                ->color('gray'),

                            TextColumn::make('barang.nama_barang')
                                ->label('Nama Barang')
                                ->formatStateUsing(fn($state) => "📦 " . $state)
                                ->weight('medium')
                                ->color('gray')
                                ->searchable()
                        ]),

                        Split::make([
                            TextColumn::make('Jumlah_barang_diajukan')
                                ->label('Jumlah Diajukan')
                                ->formatStateUsing(fn($state) => "📊 " . $state . ' unit')
                                ->color('gray')
                                ->weight('medium'),
                        ]),

                        Panel::make([
                            TextColumn::make('keterangan')
                                ->label('Keterangan')
                                ->formatStateUsing(function ($state) {
                                    if (empty($state)) {
                                        return "📝 Tidak ada keterangan";
                                    }
                                    return "📝 " . (strlen($state) > 100 ? substr($state, 0, 100) . '...' : $state);
                                })
                                ->color('gray')
                                ->size('sm')
                                ->wrap()
                                ->tooltip(fn($state) => $state)
                        ])
                            ->collapsible(false),

                        BadgeColumn::make('batch_info')
                            ->label('Batch')
                            ->formatStateUsing(function ($record) {
                                if (empty($record->batch_id)) return '';

                                $batchCount = Pengajuan::where('batch_id', $record->batch_id)->count();
                                return "Batch ({$batchCount} item)";
                            })
                            ->color('gray')
                            ->size('sm')
                            ->visible(fn($record) => !empty($record->batch_id)),

                        Split::make([
                            // PERBAIKAN: Gunakan nama relationship yang benar sesuai model
                            TextColumn::make('approvedBy.name')
                                ->label('Disetujui Oleh')
                                ->formatStateUsing(fn($state) => $state ? "✅ " . $state : '')
                                ->color('success')
                                ->visible(
                                    fn(?Model $record): bool =>
                                    $record instanceof Pengajuan && $record->status === 'approved'
                                )
                                ->tooltip(
                                    fn(Pengajuan $record) =>
                                    $record->approved_at
                                        ? 'Disetujui pada ' . Carbon::parse($record->approved_at)->format('d M Y H:i')
                                        : null
                                )
                                ->size('sm'),

                            // PERBAIKAN: Pastikan nama_project ditampilkan
                            TextColumn::make('status_barang')
                                ->label('Diperuntukan')
                                ->formatStateUsing(function ($state, $record) {
                                    $labels = [
                                        'oprasional_kantor' => 'Operasional Kantor',
                                        'project' => 'Project',
                                    ];
                                    $statusText = $labels[$state] ?? $state;

                                    // PERBAIKAN: Debug untuk melihat apakah nama_project ada
                                    if ($state === 'project') {
                                        if (!empty($record->nama_project)) {
                                            $statusText .= ' - ' . $record->nama_project;
                                        } else {
                                            $statusText .= ' (Nama project tidak diisi)'; // Debug text
                                        }
                                    }

                                    return "" . $statusText;
                                })
                                ->color('gray')
                                ->size('sm'),
                        ]),

                        Split::make([
                            // PERBAIKAN: Gunakan nama relationship yang benar
                            TextColumn::make('rejectedBy.name')
                                ->label('Ditolak Oleh')
                                ->formatStateUsing(fn($state) => $state ? "❌ " . $state : '')
                                ->color('danger')
                                ->visible(
                                    fn(?Model $record): bool =>
                                    $record instanceof Pengajuan && $record->status === 'rejected'
                                )
                                ->tooltip(
                                    fn(Pengajuan $record) =>
                                    $record->reject_reason
                                        ? "Alasan: {$record->reject_reason}"
                                        : null
                                )
                                ->size('sm'),

                            TextColumn::make('reject_reason')
                                ->label('Alasan Penolakan')
                                ->formatStateUsing(fn($state) => $state ? "🚫 " . $state : '')
                                ->color('danger')
                                ->visible(
                                    fn(?Model $record): bool =>
                                    $record instanceof Pengajuan && $record->status === 'rejected'
                                )
                                ->size('sm')
                                ->limit(30)
                                ->tooltip(fn($state) => $state),
                        ]),
                    ])->space(3),
                ])->from('md'),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                TrashedFilter::make()
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->label('Status')
                    ->indicator('Status')
                    ->preload()
                    ->searchable(),

                Tables\Filters\Filter::make('tanggal_pengajuan')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->native(false),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['dari_tanggal'] && !$data['sampai_tanggal']) {
                            return null;
                        }

                        return 'Tanggal: ' . ($data['dari_tanggal'] ? Carbon::parse($data['dari_tanggal'])->format('d/m/Y') : '...') .
                            ' hingga ' . ($data['sampai_tanggal'] ? Carbon::parse($data['sampai_tanggal'])->format('d/m/Y') : '...');
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_pengajuan', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_pengajuan', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('barang_id')
                    ->relationship('barang', 'nama_barang')
                    ->label('Barang')
                    ->searchable()
                    ->preload()
                    ->indicator('Barang'),
            ])
            ->actions([
                // APPROVE ACTION
                ActionsAction::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->label('Setujui Batch')
                    ->modalHeading('Setujui Semua Pengajuan dalam Batch')
                    ->modalDescription(function (Pengajuan $record) {
                        // Cari semua pengajuan dalam batch yang sama
                        $batchPengajuans = Pengajuan::where('batch_id', $record->batch_id)
                            ->where('status', 'pending')
                            ->with('barang')
                            ->get();

                        $totalItems = $batchPengajuans->count();
                        $itemList = $batchPengajuans->map(function ($item) {
                            return "• {$item->barang->nama_barang} ({$item->Jumlah_barang_diajukan} unit)";
                        })->join("\n");

                        return "Anda akan menyetujui {$totalItems} pengajuan barang sekaligus:\n\n{$itemList}";
                    })
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalSubmitActionLabel('Setujui Semua')
                    ->form([
                        Forms\Components\Textarea::make('keterangan_barang_keluar')
                            ->label('Keterangan Barang Keluar')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Keterangan akan diterapkan ke semua barang dalam batch ini')
                            ->rows(3),
                    ])
                    ->visible(
                        fn(Pengajuan $record) =>
                        Auth::user()->hasAnyRole(['super_admin', 'administrator']) &&
                            $record->status === 'pending'
                    )
                    ->action(function (Pengajuan $record, array $data) {
                        try {
                            // Ambil semua pengajuan dalam batch yang sama dan masih pending
                            $batchPengajuans = Pengajuan::where('batch_id', $record->batch_id)
                                ->where('status', 'pending')
                                ->with('barang')
                                ->get();

                            $approvedCount = 0;
                            $failedItems = [];

                            DB::transaction(function () use ($batchPengajuans, $data, &$approvedCount, &$failedItems, $record) {
                                foreach ($batchPengajuans as $pengajuan) {
                                    try {
                                        // Periksa stok tersedia
                                        $barang = Barang::find($pengajuan->barang_id);
                                        if ($barang->jumlah_barang < $pengajuan->Jumlah_barang_diajukan) {
                                            $failedItems[] = "{$barang->nama_barang} (stok tidak mencukupi: tersedia {$barang->jumlah_barang}, diminta {$pengajuan->Jumlah_barang_diajukan})";
                                            continue;
                                        }

                                        // Catat barang keluar
                                        $barangKeluar = Barangkeluar::create([
                                            'barang_id' => $pengajuan->barang_id,
                                            'pengajuan_id' => $pengajuan->id,
                                            'user_id' => Auth::id(),
                                            'jumlah_barang_keluar' => $pengajuan->Jumlah_barang_diajukan,
                                            'tanggal_keluar_barang' => now()->format('Y-m-d'), // Gunakan tanggal hari ini
                                            'keterangan' => $data['keterangan_barang_keluar'],
                                            'status' => $pengajuan->status_barang, // Ambil dari pengajuan
                                        ]);

                                        // Update pengajuan
                                        $pengajuan->update([
                                            'status' => 'approved',
                                            'approved_by' => Auth::id(),
                                            'approved_at' => now(),
                                        ]);

                                        // Kurangi stok barang
                                        $barang->decrement('jumlah_barang', $pengajuan->Jumlah_barang_diajukan);
                                        $approvedCount++;

                                        Log::info("Barang keluar berhasil dibuat untuk pengajuan ID: {$pengajuan->id}, tanggal: " . now()->format('Y-m-d'));
                                    } catch (\Exception $e) {
                                        $failedItems[] = "{$pengajuan->barang->nama_barang} (error: {$e->getMessage()})";
                                        Log::error('Error saat approve item individual: ' . $e->getMessage());
                                    }
                                }
                            });

                            // Notifikasi hasil
                            if ($approvedCount > 0) {
                                Notification::make()
                                    ->title('Pengajuan Berhasil Disetujui')
                                    ->body("Berhasil menyetujui {$approvedCount} pengajuan barang dalam batch {$record->batch_id}")
                                    ->success()
                                    ->send();
                            }

                            if (!empty($failedItems)) {
                                Notification::make()
                                    ->title('Beberapa Item Gagal Disetujui')
                                    ->body("Item yang gagal:\n" . implode("\n", $failedItems))
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Log::error('Error saat batch approve: ' . $e->getMessage());
                            Notification::make()
                                ->title('Gagal Menyetujui Batch')
                                ->body('Terjadi kesalahan saat memproses batch pengajuan.')
                                ->danger()
                                ->send();
                        }
                    }),

                // REJECT ACTION
                ActionsAction::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->label('Tolak Batch')
                    ->modalHeading('Tolak Semua Pengajuan dalam Batch')
                    ->modalDescription(function (Pengajuan $record) {
                        $batchPengajuans = Pengajuan::where('batch_id', $record->batch_id)
                            ->where('status', 'pending')
                            ->with('barang')
                            ->get();

                        $totalItems = $batchPengajuans->count();
                        $itemList = $batchPengajuans->map(function ($item) {
                            return "• {$item->barang->nama_barang} ({$item->Jumlah_barang_diajukan} unit)";
                        })->join("\n");

                        return "Anda akan menolak {$totalItems} pengajuan barang sekaligus:\n\n{$itemList}";
                    })
                    ->modalIcon('heroicon-o-x-circle')
                    ->modalSubmitActionLabel('Tolak Semua')
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Alasan penolakan akan diterapkan ke semua item dalam batch ini')
                            ->rows(3)
                    ])
                    ->visible(
                        fn(Pengajuan $record) =>
                        Auth::user()->hasAnyRole(['super_admin', 'administrator']) &&
                            $record->status === 'pending'
                    )
                    ->action(function (Pengajuan $record, array $data) {
                        try {
                            // Ambil semua pengajuan dalam batch yang sama dan masih pending
                            $batchPengajuans = Pengajuan::where('batch_id', $record->batch_id)
                                ->where('status', 'pending')
                                ->get();

                            $rejectedCount = 0;

                            DB::transaction(function () use ($batchPengajuans, $data, &$rejectedCount) {
                                foreach ($batchPengajuans as $pengajuan) {
                                    $pengajuan->update([
                                        'status' => 'rejected',
                                        'reject_by' => Auth::id(),
                                        'reject_reason' => $data['reject_reason'],
                                        'rejected_at' => now(),
                                    ]);
                                    $rejectedCount++;
                                }
                            });

                            Notification::make()
                                ->title('Batch Pengajuan Berhasil Ditolak')
                                ->body("Berhasil menolak {$rejectedCount} pengajuan barang dalam batch {$record->batch_id}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Log::error('Error saat batch reject: ' . $e->getMessage());
                            Notification::make()
                                ->title('Gagal Menolak Batch')
                                ->body('Terjadi kesalahan saat memproses batch pengajuan.')
                                ->danger()
                                ->send();
                        }
                    }),

                ActionGroup::make([
                    EditAction::make()
                        ->visible(
                            fn(Pengajuan $record) =>
                            $record->status === 'pending' &&
                                (Auth::user()->id === $record->user_id || Auth::user()->hasAnyRole(['super_admin', 'administrator']))
                        )
                        ->color('info'),

                    DeleteAction::make()
                        ->visible(
                            fn(Pengajuan $record) =>
                            $record->status === 'pending' &&
                                (Auth::user()->id === $record->user_id || Auth::user()->hasAnyRole(['super_admin', 'administrator']))
                        ),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'administrator'])),
                    Tables\Actions\ForceDeleteBulkAction::make()
                ]),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('Belum ada pengajuan barang')
            ->emptyStateDescription('Pengajuan barang akan muncul di sini setelah Anda atau pengguna lain membuat pengajuan.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Buat Pengajuan Baru')
                    ->url(route('filament.admin.resources.pengajuan.create'))
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
            ])
            ->poll('15s')
            ->striped()
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultSort('tanggal_pengajuan', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
