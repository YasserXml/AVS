<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanoprasionalResource\Pages;
use App\Filament\Resources\PengajuanoprasionalResource\RelationManagers;
use App\Models\Pengajuanoprasional;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Str;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PengajuanoprasionalResource extends Resource
{
    protected static ?string $model = Pengajuanoprasional::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Permintaan Barang';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Pengajuan Oprasional';

    public static function getSlug(): string
    {
        return 'permintaan/pengajuan-oprasional';
    }

    protected static ?string $pluralModelLabel = 'Pengajuan Oprasional';

    protected static ?string $modelLabel = 'Pengajuan Oprasional';

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
        ->content(function () {
                return view('pengajuann.oprasional');
            })
            ->columns([
             
            ])
            ->actions([
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
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
