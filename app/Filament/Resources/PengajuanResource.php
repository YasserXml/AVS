<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Filament\Resources\PengajuanResource\RelationManagers\DetailPengajuanRelationManager;
use App\Models\Barang;
use App\Models\Barangkeluar;
use App\Models\Pengajuan;
use App\Services\PengajuanApprovalService;
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
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Viewpengajuan;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-list';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Pengajuan Barang';

    protected static ?int $navigationSort = 8;

    protected static ?string $pluralLabel = 'Pengajuan';

    protected static ?string $slug = 'inventory/pengajuan';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? "Ada {$count} pengajuan yang menunggu persetujuan" : null;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Ambil user yang sedang login
        $user = filament()->auth()->user();

        // Jika role-nya user, hanya tampilkan pengajuan miliknya
        if ($user->hasRole('user')) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }


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
                                    ->live()
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
                                                Forms\Components\TextInput::make('nama_barang')
                                                    ->label('Nama Barang')
                                                    ->placeholder('Masukkan nama barang yang dibutuhkan')
                                                    ->required()
                                                    ->reactive()
                                                    ->live()
                                                    ->prefixIcon('heroicon-o-tag')
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('Jumlah_barang_diajukan')
                                                    ->label('Jumlah Yang Diajukan')
                                                    ->numeric()
                                                    ->required()
                                                    ->reactive()
                                                    ->live()
                                                    ->minValue(1)
                                                    ->helperText('Masukkan jumlah barang yang dibutuhkan')
                                                    ->prefixIcon('heroicon-o-shopping-cart')
                                                    ->suffix('Qty')
                                                    ->columnSpan(1),

                                                Forms\Components\Textarea::make('detail_barang')
                                                    ->label('Detail Barang')
                                                    ->placeholder('Berikan detail spesifikasi barang yang diajukan')
                                                    ->required()
                                                    ->maxLength(500)
                                                    ->rows(3)
                                                    ->columnSpanFull()
                                                    ->helperText('Berikan detail seperti spesifikasi, merek, ukuran, atau informasi penting lainnya'),
                                            ]),
                                    ])
                                    ->itemLabel(
                                        fn(array $state): ?string =>
                                        isset($state['nama_barang']) ?
                                            $state['nama_barang'] . ' (' . ($state['Jumlah_barang_diajukan'] ?? '0') . ' unit)' :
                                            'Barang Yang Diajukan'
                                    )
                                    ->collapsible()
                                    ->defaultItems(1)
                                    ->reactive()
                                    ->live()
                                    ->addActionLabel('+ Tambah Barang')
                                    ->reorderableWithButtons()
                                    ->deleteAction(
                                        fn(Forms\Components\Actions\Action $action) => $action
                                    )
                                    ->columns(1),
                            ]),
                    ])
                    ->reactive()
                    ->live(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Split::make([
                    Stack::make([
                        // Header dengan Batch Info
                        Split::make([
                            BadgeColumn::make('batch_info')
                                ->label('')
                                ->formatStateUsing(function ($record) {
                                    // Null check untuk record
                                    if (!$record || !method_exists($record, 'isPartOfGroup')) {
                                        return "📦 Item Tunggal";
                                    }

                                    if ($record->isPartOfGroup()) {
                                        $groupSize = $record->getGroupSize();
                                        return "📦 Batch ({$groupSize} item)";
                                    }
                                    return "📦 Item Tunggal";
                                })
                                ->color('info')
                                ->size('sm'),

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

                        // Info Pemohon dan Tanggal
                        Split::make([
                            TextColumn::make('user.name')
                                ->label('Pemohon')
                                ->formatStateUsing(fn($state) => $state ? "👤 " . $state : "👤 Unknown")
                                ->searchable()
                                ->color('gray')
                                ->weight('medium'),

                            TextColumn::make('tanggal_pengajuan')
                                ->label('Tanggal')
                                ->weight('medium')
                                ->formatStateUsing(fn($state) => $state ? "📅 " . Carbon::parse($state)->format('d M Y') : "📅 -")
                                ->color('gray'),
                        ]),

                        // Batch ID untuk identifikasi grup
                        TextColumn::make('batch_id')
                            ->label('ID Batch')
                            ->formatStateUsing(fn($state) => $state ? "🏷️ " . substr($state, -8) : "🏷️ -")
                            ->color('blue')
                            ->size('sm')
                            ->searchable()
                            ->toggleable(isToggledHiddenByDefault: true),

                        // Info Barang (menggunakan field baru nama_barang)
                        Split::make([
                            TextColumn::make('nama_barang')
                                ->label('Nama Barang')
                                ->formatStateUsing(fn($state) => $state ? "📦 " . $state : "📦 -")
                                ->weight('medium')
                                ->color('gray')
                                ->searchable()
                                ->wrap(),

                            TextColumn::make('Jumlah_barang_diajukan')
                                ->label('Jumlah')
                                ->formatStateUsing(fn($state) => $state ? "📊 " . $state . ' unit' : "📊 0 unit")
                                ->color('gray')
                                ->weight('medium'),
                        ]),

                        // Detail Barang (field baru)
                        Panel::make([
                            TextColumn::make('detail_barang')
                                ->label('Detail Barang')
                                ->formatStateUsing(function ($state) {
                                    if (empty($state)) {
                                        return "📝 Tidak ada detail";
                                    }
                                    return "📝 " . (strlen($state) > 80 ? substr($state, 0, 80) . '...' : $state);
                                })
                                ->color('gray')
                                ->size('sm')
                                ->wrap()
                                ->tooltip(fn($state) => $state ?: 'Tidak ada detail')
                        ])
                            ->collapsible(false),

                        // Keterangan Umum
                        Panel::make([
                            TextColumn::make('keterangan')
                                ->label('Keterangan Pengajuan')
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

                        // Status Barang dan Project
                        Split::make([
                            TextColumn::make('status_barang')
                                ->label('Diperuntukan')
                                ->formatStateUsing(function ($state, $record) {
                                    $labels = [
                                        'oprasional_kantor' => 'Operasional Kantor',
                                        'project' => 'Project',
                                    ];
                                    $statusText = $labels[$state] ?? $state;

                                    if ($state === 'project' && !empty($record->nama_project)) {
                                        $statusText .= ' - ' . $record->nama_project;
                                    }
                                    return "" . $statusText;
                                })
                                ->color('gray')
                                ->size('sm')
                                ->wrap(),
                        ]),

                        // Info Persetujuan (hanya tampil jika approved)
                        Split::make([
                            TextColumn::make('approvedBy.name')
                                ->label('Disetujui Oleh')
                                ->formatStateUsing(fn($state) => $state ? "✅ " . $state : '')
                                ->color('success')
                                ->visible(
                                    fn(?Model $record): bool =>
                                    $record && $record instanceof Pengajuan && $record->status === 'approved'
                                )
                                ->tooltip(
                                    fn(Pengajuan $record) =>
                                    $record->approved_at
                                        ? 'Disetujui pada ' . Carbon::parse($record->approved_at)->format('d M Y H:i')
                                        : null
                                )
                                ->size('sm'),
                        ]),

                        // Info Penolakan (hanya tampil jika rejected)
                        Split::make([
                            TextColumn::make('rejectedBy.name')
                                ->label('Ditolak Oleh')
                                ->formatStateUsing(fn($state) => $state ? "❌ " . $state : '')
                                ->color('danger')
                                ->visible(
                                    fn(?Model $record): bool =>
                                    $record && $record instanceof Pengajuan && $record->status === 'rejected'
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
                                    $record && $record instanceof Pengajuan && $record->status === 'rejected'
                                )
                                ->size('sm')
                                ->limit(50)
                                ->tooltip(fn($state) => $state)
                                ->wrap(),
                        ]),

                        // Info Grup Batch (tambahan untuk menunjukkan berapa item dalam batch)
                        TextColumn::make('group_info')
                            ->label('Info Grup')
                            ->formatStateUsing(function ($record) {
                                if (!$record || !method_exists($record, 'isPartOfGroup')) {
                                    return "";
                                }

                                if ($record->isPartOfGroup()) {
                                    $groupSize = $record->getGroupSize();
                                    $groupName = $record->getGroupName();
                                    return "👥 {$groupName} ({$groupSize} item)";
                                }
                                return "";
                            })
                            ->color('info')
                            ->size('sm')
                            ->visible(fn($record) => $record && method_exists($record, 'isPartOfGroup') && $record->isPartOfGroup())
                            ->toggleable(isToggledHiddenByDefault: false),

                    ])->space(3),
                ])->from('md'),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->defaultSort('created_at', 'desc')
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
            ])
            ->actions([
                // APPROVE ACTION
                ActionsAction::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->label('Setujui')
                    ->modalHeading('Setujui Pengajuan')
                    ->modalDescription(function (Pengajuan $record) {
                        $approvalService = new PengajuanApprovalService();
                        return $approvalService->generateApprovalModalDescription($record);
                    })
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalSubmitActionLabel('Setujui')
                    ->form([
                        Forms\Components\Textarea::make('keterangan_barang_keluar')
                            ->label('Keterangan Barang Keluar')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Keterangan akan diterapkan ke semua barang dalam grup pengajuan ini')
                            ->rows(3),
                    ])
                    ->visible(
                        fn(Pengajuan $record) =>
                        Auth::user()->hasAnyRole(['super_admin', 'administrator']) &&
                            $record->status === 'pending'
                    )
                    ->action(function (Pengajuan $record, array $data) {
                        $approvalService = new PengajuanApprovalService();
                        $result = $approvalService->approveBatch($record, $data);

                        if (!$result['success']) {
                            Log::error('Group approval failed: ' . ($result['error'] ?? 'Unknown error'));
                        }
                    }),
                // REJECT ACTION
                ActionsAction::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->label('Tolak')
                    ->modalHeading('Tolak Pengajuan')
                    ->modalDescription(function (Pengajuan $record) {
                        $approvalService = new PengajuanApprovalService();
                        return $approvalService->generateRejectionModalDescription($record);
                    })
                    ->modalIcon('heroicon-o-x-circle')
                    ->modalSubmitActionLabel('Tolak')
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Alasan penolakan akan diterapkan ke semua item dalam grup pengajuan ini')
                            ->rows(3)
                    ])
                    ->visible(
                        fn(Pengajuan $record) =>
                        Auth::user()->hasAnyRole(['super_admin', 'administrator']) &&
                            $record->status === 'pending'
                    )
                    ->action(function (Pengajuan $record, array $data) {
                        $approvalService = new PengajuanApprovalService();
                        $result = $approvalService->rejectBatch($record, $data);

                        if (!$result['success']) {
                            Log::error('Group rejection failed: ' . ($result['error'] ?? 'Unknown error'));
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
                        ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'administrator']))
                        ->label('Hapus Dipilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->modalHeading('Hapus Pengguna Yang Dipilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua pengguna yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'administrator'])),
                ])
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->color('danger')
                    ->tooltip('Aksi untuk pengguna yang dipilih'),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('Belum ada pengajuan barang')
            ->emptyStateDescription('Pengajuan barang akan muncul di sini setelah Anda atau pengguna lain membuat pengajuan.')
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
