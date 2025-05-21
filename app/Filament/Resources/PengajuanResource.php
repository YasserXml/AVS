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
    //     $user = Filament::auth()->user();

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
                                        'oprasional_kantor' => 'Operasional Kantor', // Pastikan nilainya sama dengan yang di DB
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
                                // Repeater untuk pengajuan multiple barang
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
                                                                $set('kategoris_id', $barang->kategori_id); // Sesuaikan dengan nama field di tabel pengajuans
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

                                                // Hidden field untuk kategori_id
                                                Forms\Components\Hidden::make('kategoris_id'),

                                                Forms\Components\TextInput::make('jumlah_barang')
                                                    ->label('Stok Tersedia')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->prefix('Tersedia:')
                                                    ->prefixIcon('heroicon-o-clipboard-document-check')
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('jumlah_diajukan')
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

                                                Forms\Components\Textarea::make('catatan')
                                                    ->label('Catatan')
                                                    ->placeholder('Catatan tambahan untuk barang ini (opsional)')
                                                    ->rows(2)
                                                    ->columnSpan(2),
                                            ]),
                                    ])
                                    ->itemLabel(
                                        fn(array $state): ?string =>
                                        isset($state['barang_id']) && isset($state['nama_barang']) ?
                                            $state['nama_barang'] . ' (' . ($state['jumlah_diajukan'] ?? '0') . ' unit)' :
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
            // ->recordUrl(false)
            ->columns([
                Split::make([
                    // Kolom Kiri - Informasi Pengajuan
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
                                ->color('info')
                                ->weight('medium'),

                            TextColumn::make('tanggal_pengajuan')
                                ->label('Tanggal')
                                ->weight('medium')
                                ->formatStateUsing(fn($state) => "📅 " . Carbon::parse($state)->format('d M Y'))
                                ->color('gray'),

                            TextColumn::make('barang.nama_barang')
                                ->label('Nama Barang')
                                ->formatStateUsing(fn($state)=> "📦 ". $state)
                                ->weight('medium')
                                ->searchable()
                        ]),
                        // Menambahkan keterangan
                        Panel::make([
                            TextColumn::make('keterangan')
                                ->label('Keterangan Pengajuan')
                                ->formatStateUsing(fn($state) => "📝 " . $state)
                                ->color('gray')
                                ->size('sm')
                                ->wrap()
                        ])
                            ->collapsible(false),

                        Split::make([
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

                            TextColumn::make('status_barang')
                                ->label('Diperuntukan')
                                ->formatStateUsing(function ($state) {
                                    $labels = [
                                        'operasional_kantor' => 'Operasional Kantor',
                                        'project' => 'Project',
                                    ];
                                    return "🔄 " . ($labels[$state] ?? $state);
                                })
                                ->color('info')
                                ->size('sm'),
                        ]),

                        Split::make([
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
                ActionsAction::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->label('Setujui')
                    ->modalHeading('Setujui Pengajuan Barang')
                    ->modalDescription(fn(Pengajuan $record) => "Anda akan menyetujui pengajuan barang \"{$record->barang->nama_barang}\" sejumlah {$record->Jumlah_barang_diajukan} unit.")
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalSubmitActionLabel('Setujui Pengajuan')
                    ->form([
                        Section::make('Informasi Persetujuan')
                            ->icon('heroicon-o-check-badge')
                            ->columns(1)
                            ->schema([
                                Radio::make('status_penggunaan')
                                    ->label('Status Penggunaan')
                                    ->options([
                                        'operasional_kantor' => 'Operasional Kantor',
                                        'project' => 'Project',
                                    ])
                                    ->default('operasional_kantor')
                                    ->required()
                                    ->inline()
                                    ->helperText('Pilih status penggunaan barang'),

                                Textarea::make('keterangan_barang_keluar')
                                    ->label('Keterangan Barang Keluar')
                                    ->default(fn(Pengajuan $record) => $record->keterangan)
                                    ->required()
                                    ->rows(3)
                                    ->helperText('Keterangan ini akan dicatat pada barang keluar')
                            ])
                    ])
                    ->visible(
                        fn(Pengajuan $record) =>
                        Auth::user()->hasAnyRole(['super_admin', 'administrator']) &&
                            $record->status === 'pending'
                    )
                    ->action(function (Pengajuan $record, array $data) {
                        try {
                            // Periksa stok tersedia
                            $barang = Barang::find($record->barang_id);
                            if ($barang->jumlah_barang < $record->Jumlah_barang_diajukan) {
                                Notification::make()
                                    ->title('Stok Tidak Mencukupi')
                                    ->body("Stok barang \"{$barang->nama_barang}\" hanya tersedia {$barang->jumlah_barang} unit.")
                                    ->danger()
                                    ->persistent()
                                    ->send();
                                return;
                            }

                            DB::transaction(function () use ($record, $data, $barang) {
                                // 1. Catat barang keluar
                                $barangKeluar = Barangkeluar::create([
                                    'barang_id' => $record->barang_id,
                                    'pengajuan_id' => $record->id,
                                    'user_id' => Auth::id(),
                                    'jumlah_barang_keluar' => $record->Jumlah_barang_diajukan,
                                    'tanggal_keluar_barang' => now(),
                                    'keterangan' => $data['keterangan_barang_keluar'],
                                    'status' => $data['status_penggunaan'],
                                ]);

                                // 2. Update pengajuan
                                $record->update([
                                    'status' => 'approved',
                                    'approved_by' => Auth::id(),
                                    'approved_at' => now(),
                                    'barang_keluar_id' => $barangKeluar->id,
                                ]);

                                // 3. Kurangi stok barang
                                $barang->decrement('jumlah_barang', $record->Jumlah_barang_diajukan);
                            });

                            Notification::make()
                                ->title('Pengajuan Disetujui')
                                ->body("Pengajuan barang \"{$barang->nama_barang}\" berhasil disetujui!")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Log::error('Error saat menyetujui pengajuan: ' . $e->getMessage());
                            Notification::make()
                                ->title('Gagal Menyetujui Pengajuan')
                                ->body('Terjadi kesalahan saat memproses pengajuan. Silakan coba lagi.')
                                ->danger()
                                ->send();
                        }
                    }),

                ActionsAction::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->label('Tolak')
                    ->modalHeading('Tolak Pengajuan Barang')
                    ->modalDescription(fn(Pengajuan $record) => "Anda akan menolak pengajuan barang \"{$record->barang->nama_barang}\" sejumlah {$record->Jumlah_barang_diajukan} unit.")
                    ->modalIcon('heroicon-o-x-circle')
                    ->modalSubmitActionLabel('Tolak Pengajuan')
                    ->form([
                        Section::make('Alasan Penolakan')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->schema([
                                Textarea::make('reject_reason')
                                    ->label('Alasan Penolakan')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Berikan alasan mengapa pengajuan ini ditolak')
                                    ->rows(3)
                            ])
                    ])
                    ->visible(
                        fn(Pengajuan $record) =>
                        Auth::user()->hasAnyRole(['super_admin', 'administrator']) &&
                            $record->status === 'pending'
                    )
                    ->action(function (Pengajuan $record, array $data) {
                        try {
                            $record->update([
                                'status' => 'rejected',
                                'reject_by' => Auth::id(),
                                'reject_reason' => $data['reject_reason'],
                                'rejected_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Pengajuan Ditolak')
                                ->body("Pengajuan barang \"{$record->barang->nama_barang}\" telah ditolak.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Log::error('Error saat menolak pengajuan: ' . $e->getMessage());
                            Notification::make()
                                ->title('Gagal Menolak Pengajuan')
                                ->body('Terjadi kesalahan saat memproses pengajuan. Silakan coba lagi.')
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
        return [
            
        ];
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
