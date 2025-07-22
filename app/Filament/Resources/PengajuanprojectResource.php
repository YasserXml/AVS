<?php

namespace App\Filament\Resources;

use App\Filament\Actions\PengajuanProjectActions;
use App\Filament\Resources\PengajuanprojectResource\Pages;
use App\Filament\Resources\PengajuanprojectResource\RelationManagers;
use App\Models\Nameproject;
use App\Models\Pengajuanproject;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use ZipArchive;

class PengajuanprojectResource extends Resource
{
    protected static ?string $model = Pengajuanproject::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $activeNavigationIcon = 'heroicon-s-clipboard-document-list';

    protected static ?string $navigationGroup = 'Permintaan Barang';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Pengajuan Project';

    public static function getSlug(): string
    {
        return 'permintaan/pengajuan-project';
    }

    protected static ?string $pluralModelLabel = 'Pengajuan Project';

    protected static ?string $modelLabel = 'Pengajuan Project';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = filament()->auth()->user();

        // Role purchasing - dapat melihat status terkait pengadaan
        if ($user->hasRole('purchasing')) {
            return $query->whereIn('status', [
                'disetujui_pm_dikirim_ke_pengadaan',     // Dari action setujuiDanKirimKePengadaan
                'disetujui_pengadaan',                   // Dari action setujuiPengadaan
                'ditolak_pengadaan',                     // Dari action tolakPengadaan
                'pengajuan_dikirim_ke_pengadaan_final',  // Dari action kirimKembaliKePengadaan
                'cancelled'
            ]);
        }

        // Role admin - dapat melihat status terkait admin dan proses akhir
        if ($user->hasRole('admin')) {
            return $query->whereIn('status', [
                'pengajuan_terkirim',                    // Status awal yang muncul di actions
                'pending_admin_review',                  // Status yang sudah ada
                'pengajuan_dikirim_ke_admin',           // Dari action kirimKeAdmin
                'processing',                           // Dari action mulaiProsesPengadaan
                'ready_pickup',                         // Dari action siapDiambil
                'completed',                            // Dari action selesai
                'cancelled'
            ]);
        }

        // Role direktur_keuangan - dapat melihat status terkait direksi
        if ($user->hasRole('direktur_keuangan')) {
            return $query->whereIn('status', [
                'pengajuan_dikirim_ke_direksi',         // Dari action kirimKeDireksi
                'approved_by_direksi',                  // Dari action approveDireksi
                'reject_direksi',                       // Dari action rejectDireksi
                'cancelled'
            ]);
        }

        // Role keuangan - dapat melihat status terkait keuangan
        if ($user->hasRole('keuangan')) {
            return $query->whereIn('status', [
                'pengajuan_dikirim_ke_keuangan',        // Dari action kirimKeKeuangan
                'pending_keuangan',                     // Dari action reviewKeuangan
                'process_keuangan',                     // Dari action prosesKeuangan
                'execute_keuangan',                     // Dari action executeKeuangan
                'cancelled'
            ]);
        }

        // Role PM atau user biasa - hanya dapat melihat pengajuan mereka sendiri
        return $query->where('user_id', $user->id);
    }
    
    protected function applyUserFilter(Builder $query): Builder
    {
        $user = filament()->auth()->user();

        // Jika user memiliki role khusus, tidak perlu filter tambahan
        if ($user->hasAnyRole(['purchasing', 'admin', 'direktur_keuangan', 'keuangan'])) {
            return $query;
        }

        // Untuk user biasa atau PM, pastikan hanya data mereka sendiri yang tampil
        return $query->where('user_id', $user->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pengajuan')
                    ->description('Lengkapi informasi dasar pengajuan barang untuk project')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible(false)
                    ->schema([
                        Card::make()
                            ->schema([
                                Hidden::make('user_id')
                                    ->default(fn() => filament()->auth()->id()),

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
                                            ->helperText('Pilih tanggal kapan barang dibutuhkan untuk project'),
                                    ]),

                                Forms\Components\Grid::make(['sm' => 1, 'md' => 2])
                                    ->schema([
                                        Forms\Components\Select::make('project_id')
                                            ->label('Pilih Project')
                                            ->relationship('nameproject', 'nama_project')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->dehydrated(true)
                                            ->reactive()
                                            ->placeholder('Pilih project untuk pengajuan barang...')
                                            ->prefixIcon('heroicon-o-building-office')
                                            ->helperText('Pilih project yang akan diajukan barang')
                                            ->native(false)
                                            ->columnSpan(1)
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                if ($state) {
                                                    // Ambil data project dengan relasi user (PM)
                                                    $project = Nameproject::with('user')->find($state);

                                                    if ($project && $project->user) {
                                                        $set('pm_name', $project->user->name);
                                                    } else {
                                                        $set('pm_name', 'Tidak ada PM ditugaskan');
                                                    }
                                                } else {
                                                    $set('pm_name', '');
                                                }
                                            }),

                                        Forms\Components\TextInput::make('pm_name')
                                            ->label('Project Manager')
                                            ->disabled()
                                            ->reactive()
                                            ->live()
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-o-user-circle')
                                            ->placeholder('PM akan muncul setelah memilih project')
                                            ->helperText('Nama PM akan otomatis muncul setelah memilih project')
                                            ->columnSpan(1),
                                    ]),
                            ]),
                    ]),

                Section::make('Detail Barang Project')
                    ->description('Tambahkan satu atau lebih barang yang dibutuhkan untuk project, lengkapi dengan file pendukung')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Forms\Components\Repeater::make('detail_barang')
                            ->label('Daftar Barang Project')
                            ->schema([
                                Forms\Components\Grid::make(['sm' => 1, 'md' => 2])
                                    ->schema([
                                        Forms\Components\TextInput::make('nama_barang')
                                            ->label('Nama Barang')
                                            ->placeholder('Masukkan nama barang yang dibutuhkan untuk project')
                                            ->required()
                                            ->live()
                                            ->prefixIcon('heroicon-o-tag')
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('jumlah_barang_diajukan')
                                            ->label('Jumlah Yang Diajukan')
                                            ->numeric()
                                            ->required()
                                            ->live()
                                            ->dehydrated(true)
                                            ->helperText('Masukkan jumlah barang yang dibutuhkan')
                                            ->prefixIcon('heroicon-o-shopping-cart')
                                            ->suffix('Unit')
                                            ->columnSpan(1),

                                        Forms\Components\Textarea::make('keterangan_barang')
                                            ->label('Detail/Spesifikasi Barang')
                                            ->placeholder('Berikan detail spesifikasi barang yang diajukan untuk project (merek, ukuran, tipe, dll)')
                                            ->required()
                                            ->maxLength(500)
                                            ->rows(3)
                                            ->live()
                                            ->columnSpanFull()
                                            ->helperText('Berikan detail seperti spesifikasi, merek, ukuran, atau informasi penting lainnya'),

                                        Forms\Components\FileUpload::make('file_barang')
                                            ->label('File Pendukung Barang')
                                            ->multiple()
                                            ->live()
                                            ->directory('pengajuan-project/barang')
                                            ->preserveFilenames(true)
                                            ->maxSize(5120)
                                            ->helperText('Upload file pendukung untuk barang ini seperti gambar, spesifikasi, atau dokumen lainnya (max 5MB per file)')
                                            ->columnSpanFull()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->uploadingMessage('Mengupload file...')
                                            ->panelLayout('compact')
                                            ->imagePreviewHeight('150')
                                            ->loadingIndicatorPosition('left')
                                            ->removeUploadedFileButtonPosition('right')
                                            ->uploadButtonPosition('left')
                                            ->uploadProgressIndicatorPosition('left'),
                                    ]),
                            ])
                            ->itemLabel(
                                fn(array $state): ?string =>
                                isset($state['nama_barang']) ?
                                    $state['nama_barang'] . ' (' . ($state['jumlah_barang_diajukan'] ?? '0') . ' unit)' :
                                    'Barang Project'
                            )
                            ->collapsible()
                            ->defaultItems(1)
                            ->live()
                            ->addActionLabel('+ Tambah Barang?')
                            ->reorderableWithButtons()
                            ->deleteAction(
                                fn(Forms\Components\Actions\Action $action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('Hapus Barang')
                                    ->modalDescription('Apakah Anda yakin ingin menghapus barang yang akan diajukan?')
                                    ->modalSubmitActionLabel('Hapus')
                            )
                            ->columns(1)
                            ->visible(fn(Get $get) => $get('project_id'))
                            ->reactive()
                            ->live(),
                    ]),

                Section::make('File Pendukung Project')
                    ->description('Upload file pendukung umum untuk project ini')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('uploaded_files')
                            ->label('File Pendukung Project')
                            ->multiple()
                            ->live()
                            ->directory('pengajuan-project/umum')
                            ->preserveFilenames(true)
                            ->maxSize(5120) // 5MB
                            ->helperText('Upload file pendukung umum project atau dokumen lainnya (max 5MB per file)')
                            ->columnSpanFull()
                            ->disk('public')
                            ->visibility('public')
                            ->uploadingMessage('Mengupload file...')
                            ->panelLayout('compact')
                            ->imagePreviewHeight('150')
                            ->loadingIndicatorPosition('left')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->uploadProgressIndicatorPosition('left'),
                    ])
                    ->visible(fn(Get $get) => $get('project_id'))
                    ->reactive()
                    ->live(),
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
                                ->weight(FontWeight::Medium)
                        ])->space(1),

                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('tanggal_pengajuan')
                                ->label('')
                                ->formatStateUsing(fn($state) => "📅 Tanggal Pengajuan: " . Carbon::parse($state)->format('d M Y')),
                        ])->space(1)->alignEnd(),
                    ]),

                    // Informasi Project
                    Tables\Columns\Layout\Panel::make([
                        Tables\Columns\Layout\Split::make([
                            Tables\Columns\Layout\Stack::make([
                                Tables\Columns\TextColumn::make('nameproject.nama_project')
                                    ->label('')
                                    ->formatStateUsing(fn($state) => "🏢 Project: {$state}")
                                    ->weight(FontWeight::Medium),
                            ])->space(1),

                            Tables\Columns\Layout\Stack::make([
                                Tables\Columns\TextColumn::make('nameproject.user.name')
                                    ->label('')
                                    ->formatStateUsing(fn($state) => $state ? "👨‍💼 Project Manager: {$state}" : "👨‍💼 Project Manager: Tidak ada PM"),
                            ])->space(1)->alignEnd(),
                        ])->from('md'),
                    ])->collapsible(),

                    Tables\Columns\Layout\Panel::make([
                        Tables\Columns\ViewColumn::make('progress')
                            ->view('pengajuann.track')
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
            ->poll('10s')
            ->deferLoading()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Pengajuan Pertama')
                    ->icon('heroicon-m-plus'),
            ])
            ->emptyStateDescription('Belum ada pengajuan project yang dibuat.')
            ->emptyStateHeading('Tidak ada pengajuan project')
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
                        ->modalHeading('Hapus Data Pengajuan Project')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->color('gray')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->label('Aksi')
                    ->size('sm'),

                PengajuanProjectActions::terimaDanReview(),
                PengajuanProjectActions::setujuiDanKirimKePengadaan(),
                PengajuanProjectActions::tolakPengajuan(),
                PengajuanProjectActions::setujuiPengadaan(),
                PengajuanProjectActions::tolakPengadaan(),
                PengajuanProjectActions::kirimKeDireksi(),
                PengajuanProjectActions::approveDireksi(),
                PengajuanProjectActions::rejectDireksi(),
                PengajuanProjectActions::kirimKeKeuangan(),
                PengajuanProjectActions::reviewKeuangan(),
                PengajuanProjectActions::prosesKeuangan(),
                PengajuanProjectActions::executeKeuangan(),
                PengajuanProjectActions::kirimKembaliKePengadaan(),
                PengajuanProjectActions::kirimKeAdmin(),
                PengajuanProjectActions::mulaiProsesPengadaan(),
                PengajuanProjectActions::siapDiambil(),
                PengajuanProjectActions::selesai(),

                Tables\Actions\Action::make('detail')
                    ->label('Lihat Detail Barang')
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detail Pengajuan Project')
                    ->modalWidth('7xl')
                    ->modalContent(function ($record) {
                        return view('pengajuann.detail-project', [
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
                            ->visible(function ($record) {
                                $hasUploadedFiles = !empty($record->uploaded_files);
                                $hasBarangFiles = false;

                                if (!empty($record->detail_barang)) {
                                    foreach ($record->detail_barang as $barang) {
                                        if (!empty($barang['file_barang'])) {
                                            $hasBarangFiles = true;
                                            break;
                                        }
                                    }
                                }

                                return $hasUploadedFiles || $hasBarangFiles;
                            })
                            ->action(function ($record) {
                                // Kumpulkan semua file dari uploaded_files dan file_barang
                                $allFiles = [];

                                // Tambahkan file dari uploaded_files
                                if (!empty($record->uploaded_files)) {
                                    $allFiles = array_merge($allFiles, $record->uploaded_files);
                                }

                                // Tambahkan file dari detail_barang
                                if (!empty($record->detail_barang)) {
                                    foreach ($record->detail_barang as $barang) {
                                        if (!empty($barang['file_barang'])) {
                                            $allFiles = array_merge($allFiles, $barang['file_barang']);
                                        }
                                    }
                                }

                                if (empty($allFiles)) {
                                    Notification::make()
                                        ->title('Tidak Ada File')
                                        ->body('Tidak ada file yang tersedia untuk diunduh.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                if (count($allFiles) === 1) {
                                    $filePath = storage_path('app/public/' . $allFiles[0]);
                                    if (file_exists($filePath)) {
                                        return response()->download($filePath, basename($allFiles[0]));
                                    }
                                } else {
                                    $zip = new ZipArchive();
                                    $zipFileName = 'pengajuan_project_' . $record->id . '_files_' . date('Y-m-d_H-i-s') . '.zip';
                                    $zipPath = storage_path('app/temp/' . $zipFileName);

                                    if (!file_exists(storage_path('app/temp'))) {
                                        mkdir(storage_path('app/temp'), 0755, true);
                                    }

                                    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                                        foreach ($allFiles as $file) {
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

                // Action untuk lihat history
                Tables\Actions\Action::make('lihat_history')
                    ->label('Lihat Riwayat')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->modalHeading('Riwayat Status Pengajuan Project')
                    ->modalWidth('2xl')
                    ->modalContent(function ($record) {
                        $history = $record->status_history ?? [];

                        if (empty($history)) {
                            return view('pengajuann.projecthistory.empty-history');
                        }

                        // Sort history by created_at descending (newest first)
                        usort($history, function ($a, $b) {
                            return strtotime($b['created_at']) - strtotime($a['created_at']);
                        });

                        return view('pengajuann.projecthistory.status-history', [
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
                        ->modalHeading('Hapus Permanen Pengajuan Project')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengajuan project ini secara permanen?'),
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
            'index' => Pages\ListPengajuanprojects::route('/'),
            'create' => Pages\CreatePengajuanproject::route('/create'),
            'edit' => Pages\EditPengajuanproject::route('/{record}/edit'),
        ];
    }
}
