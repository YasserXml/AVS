<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanprojectResource\Pages;
use App\Filament\Resources\PengajuanprojectResource\RelationManagers;
use App\Models\Nameproject;
use App\Models\Pengajuanproject;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                            ->visible(fn(Get $get) => $get('project_id')),
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
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPengajuanprojects::route('/'),
            'create' => Pages\CreatePengajuanproject::route('/create'),
            'edit' => Pages\EditPengajuanproject::route('/{record}/edit'),
        ];
    }
}
