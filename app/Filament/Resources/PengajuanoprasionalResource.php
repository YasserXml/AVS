<?php

namespace App\Filament\Resources;

use App\Filament\Actions\PengajuanActions;
use App\Filament\Resources\PengajuanoprasionalResource\Pages;
use App\Filament\Resources\PengajuanoprasionalResource\RelationManagers;
use App\Models\Pengajuanoprasional;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Forms\Form;
use Filament\Infolists\Components\Card as ComponentsCard;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Str;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use ZipArchive;

class PengajuanoprasionalResource extends Resource
{
    protected static ?string $model = Pengajuanoprasional::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $activeNavigationIcon = 'heroicon-s-clipboard-document-list';

    protected static ?string $navigationGroup = 'Permintaan Barang';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Pengajuan Oprasional';

    public static function getSlug(): string
    {
        return 'permintaan/pengajuan-oprasional';
    }

    protected static ?string $pluralModelLabel = 'Pengajuan Oprasional';

    protected static ?string $modelLabel = 'Pengajuan Oprasional';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = filament()->auth()->user();
        if ($user->hasRole('purchasing')) {
            return $query->whereIn('status', [
                'diajukan_ke_superadmin',
                'superadmin_approved',
                'superadmin_rejected',
                'pengajuan_dikirim_ke_pengadaan',
                'cancelled'
            ]);
        }
        if ($user->hasRole('admin')) {
            return $query->whereIn('status', [
                'pengajuan_terkirim',
                'pending_admin_review',
                'pengajuan_dikirim_ke_admin',
                'processing',
                'ready_pickup',
                'cancelled',
                'completed',
            ]);
        }
        if ($user->hasRole('direktur_keuangan')) {
            return $query->whereIn('status', [
                'pengajuan_dikirim_ke_direksi',
                'approved_by_direksi',
                'cancelled',
            ]);
        }
        if ($user->hasRole('keuangan')) {
            return $query->whereIn('status', [
                'pengajuan_dikirim_ke_keuangan',
                'pending_keuangan',
                'process_keuangan',
                'execute_keuangan',
                'cancelled',
            ]);
        }
        return $query->where('user_id', $user->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ComponentsSection::make('Informasi Pengajuan')
                    ->description('Lengkapi informasi dasar pengajuan barang')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible(false)
                    ->schema([
                        Card::make()
                            ->schema([
                                Hidden::make('user_id')
                                    ->default(fn() => Filament::auth()->id()),

                                Forms\Components\Grid::make(['sm' => 1, 'md' => 2])
                                    ->schema([
                                        Forms\Components\DatePicker::make('tanggal_pengajuan')
                                            ->label('Tanggal Pengajuan')
                                            ->required()
                                            ->default(now())
                                            ->native(false)
                                            ->disabled()
                                            ->dehydrated(true)
                                            ->displayFormat('d F Y')
                                            ->prefixIcon('heroicon-o-calendar'),

                                        Forms\Components\DatePicker::make('tanggal_dibutuhkan')
                                            ->label('Tanggal Dibutuhkan')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d F Y')
                                            ->prefixIcon('heroicon-o-calendar')
                                            ->helperText('Pilih tanggal kapan barang dibutuhkan'),
                                    ]),
                            ]),
                    ]),

                ComponentsSection::make('Detail Barang yang Diajukan')
                    ->description('Tambahkan satu atau lebih barang yang ingin diajukan')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Forms\Components\Repeater::make('detail_barang')
                            ->label('Daftar Barang')
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

                                        Forms\Components\TextInput::make('jumlah_barang_diajukan')
                                            ->label('Jumlah Yang Diajukan')
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->live()
                                            ->dehydrated(true)
                                            ->helperText('Masukkan jumlah barang yang dibutuhkan')
                                            ->prefixIcon('heroicon-o-shopping-cart')
                                            ->suffix('Unit')
                                            ->columnSpan(1),

                                        Forms\Components\Textarea::make('keterangan_barang')
                                            ->label('Detail/Spesifikasi Barang')
                                            ->placeholder('Berikan detail spesifikasi barang yang diajukan (merek, ukuran, tipe, dll)')
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
                                    $state['nama_barang'] . ' (' . ($state['jumlah_barang_diajukan'] ?? '0') . ' unit)' :
                                    'Barang Yang Diajukan'
                            )
                            ->collapsible()
                            ->defaultItems(1)
                            ->reactive()
                            ->live()
                            ->addActionLabel('+ Tambah Barang?')
                            ->reorderableWithButtons()
                            ->deleteAction(
                                fn(Forms\Components\Actions\Action $action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('Hapus Barang')
                                    ->modalDescription('Apakah Anda yakin ingin menghapus list barang ini?')
                                    ->modalSubmitActionLabel('Hapus')
                            )
                            ->columns(1),
                    ]),

                ComponentsSection::make('File Pendukung')
                    ->description('Upload file pendukung jika diperlukan')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('uploaded_files')
                            ->label('File Pendukung')
                            ->multiple()
                            ->directory('pengajuan-operasional')
                            ->preserveFilenames(true)
                            ->maxSize(5120) // 5MB
                            ->helperText('Upload file pendukung seperti gambar, spesifikasi, atau dokumen lainnya (max 5MB per file)')
                            ->columnSpanFull()
                            ->directory('public')
                            ->disk('public')
                            ->visibility('public')
                            ->uploadingMessage('Mengupload file...')
                            ->panelLayout('compact'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('user.name')
                                ->label('')
                                ->formatStateUsing(fn($state) => "👤 Yang Mengajukan: {$state}")
                                ->color('gray')
                                ->weight(FontWeight::Medium)
                        ])->space(1),

                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('tanggal_pengajuan')
                                ->label('')
                                ->formatStateUsing(fn($state) => "📅 Tanggal Pengajuan: " . Carbon::parse($state)->format('d M Y'))
                                ->color('gray'),
                        ])->space(1)->alignEnd(),
                    ]),

                    Tables\Columns\Layout\Panel::make([
                        Tables\Columns\ViewColumn::make('progress')
                            ->view('pengajuann.progress')
                            ->state(fn($record) => [
                                'status' => $record->status,
                                'percentage' => $record->getProgressPercentage(),
                                'color' => 'blue'
                            ]),
                    ])->collapsible(),
                    // Informasi Tanggal & Timeline
                    Tables\Columns\Layout\Panel::make([
                        Tables\Columns\Layout\Split::make([
                            Tables\Columns\Layout\Stack::make([
                                Tables\Columns\TextColumn::make('tanggal_dibutuhkan')
                                    ->label('')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state) return '⏰ Tanggal Dibutuhkan: Tidak ditentukan';
                                        return '⏰ Tanggal Dibutuhkan: ' . Carbon::parse($state)->format('d M Y');
                                    })
                                    ->color('warning'),
                            ])->space(1),
                        ])->from('md'),
                    ])->collapsible(),

                    // Reject Reason (hanya jika ditolak)
                    Tables\Columns\Layout\Panel::make([
                        Tables\Columns\TextColumn::make('reject_reason')
                            ->label('')
                            ->formatStateUsing(fn($state) => $state ? "❌ Alasan Penolakan: {$state}" : "❌ Alasan Penolakan: Tidak ada alasan")
                            ->color('danger')
                            ->wrap()
                            ->tooltip(fn($state) => $state),
                    ])
                        ->collapsible()
                        ->collapsed()
                        ->visible(fn($record) => $record->rejected_by !== null),

                ])->space(3)
                    ->extraAttributes([
                        'class' => 'min-w-0 flex-1'
                    ]),
            ])
            ->contentGrid([
                'md' => 1,
                'lg' => 1,
                'xl' => 2,
                '2xl' => 2,
            ])
            ->defaultSort('created_at', 'desc')
            ->striped(false)
            ->paginated([12, 24, 48, 96])
            ->extremePaginationLinks()
            ->poll('30s')
            ->deferLoading()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Pengajuan Pertama')
                    ->icon('heroicon-m-plus'),
            ])
            ->emptyStateDescription('Belum ada pengajuan operasional yang dibuat.')
            ->emptyStateHeading('Tidak ada pengajuan')
            ->emptyStateIcon('heroicon-o-document-plus')
            ->filters([
                TrashedFilter::make()
                    ->label('Termasuk yang Dihapus'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('warning')
                        ->icon('heroicon-o-pencil-square'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Pengajuan')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->color('gray')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->label('Aksi')
                    ->size('sm'),
                Tables\Actions\Action::make('detail')
                    ->label('Lihat Detail Barang')
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detail Pengajuan')
                    ->modalWidth('7xl')
                    ->modalContent(function ($record) {
                        return view('pengajuann.detail-pengajuan', [
                            'record' => $record,
                            'detailBarang' => $record->detail_barang ?? [],
                            'uploadedFiles' => $record->uploaded_files ?? [],
                        ]);
                    })
                    ->modalFooterActions([
                        Tables\Actions\Action::make('download_all_files')
                            ->label('Download Semua File')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->visible(fn($record) => !empty($record->uploaded_files))
                            ->action(function ($record) {
                                $files = $record->uploaded_files;

                                if (empty($files)) {
                                    Notification::make()
                                        ->title('Tidak Ada File')
                                        ->body('Tidak ada file yang tersedia untuk diunduh.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                if (count($files) === 1) {
                                    $filePath = storage_path('app/public/' . $files[0]);
                                    if (file_exists($filePath)) {
                                        return response()->download($filePath, basename($files[0]));
                                    }
                                } else {
                                    $zip = new ZipArchive();
                                    $zipFileName = 'pengajuan_' . $record->id . '_files_' . date('Y-m-d_H-i-s') . '.zip';
                                    $zipPath = storage_path('app/temp/' . $zipFileName);

                                    if (!file_exists(storage_path('app/temp'))) {
                                        mkdir(storage_path('app/temp'), 0755, true);
                                    }

                                    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                                        foreach ($files as $file) {
                                            $filePath = storage_path('app/public/' . $file);
                                            if (file_exists($filePath)) {
                                                $zip->addFile($filePath, basename($file));
                                            }
                                        }
                                        $zip->close();

                                        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
                                    }
                                }

                                Notification::make()
                                    ->title('Gagal Mengunduh File')
                                    ->body('File tidak ditemukan atau terjadi kesalahan.')
                                    ->danger()
                                    ->send();
                            }),
                    ]),

                PengajuanActions::mulaiReview(),
                PengajuanActions::kirimKeSuperAdmin(),
                PengajuanActions::approveSuperAdmin(),
                PengajuanActions::rejectSuperAdmin(),
                PengajuanActions::kirimKeDireksi(),
                PengajuanActions::approveDireksi(),
                PengajuanActions::rejectDireksi(),
                PengajuanActions::kirimKeKeuangan(),
                PengajuanActions::reviewKeuangan(),
                PengajuanActions::prosesKeuangan(),
                PengajuanActions::executeKeuangan(),
                PengajuanActions::kirimKePengadaan(),
                PengajuanActions::kirimKeAdmin(),
                PengajuanActions::mulaiProses(),
                PengajuanActions::siapDiambil(),
                PengajuanActions::selesai(),

                // Action untuk lihat history
                Tables\Actions\Action::make('lihat_history')
                    ->label('Lihat Riwayat')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->modalHeading('Riwayat Status Pengajuan')
                    ->modalWidth('2xl')
                    ->modalContent(function ($record) {
                        $history = $record->status_history ?? [];

                        if (empty($history)) {
                            return view('pengajuann.history.empty-status');
                        }

                        usort($history, function ($a, $b) {
                            return strtotime($b['created_at']) - strtotime($a['created_at']);
                        });

                        return view('pengajuann.history.status-history', [
                            'history' => $history,
                            'record' => $record
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Pengajuan')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengajuan ini secara permanen?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([15, 25, 50, 100]);
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
            'index' => Pages\ListPengajuanoprasionals::route('/'),
            'create' => Pages\CreatePengajuanoprasional::route('/create'),
            'edit' => Pages\EditPengajuanoprasional::route('/{record}/edit'),
        ];
    }
}
