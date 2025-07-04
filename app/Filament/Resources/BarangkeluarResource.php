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
                    ->aside()
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


                        // Keterangan
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Masukkan keterangan barang keluar (opsional)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                // Detail Barang Section
                Forms\Components\Section::make()
                    ->heading('Detail Barang Keluar')
                    ->description('Pilih barang yang akan dikeluarkan dari inventori')
                    ->icon('heroicon-o-cube-transparent')
                    ->collapsible()
                    ->collapsed(false)
                    ->aside()
                    ->compact()
                    ->schema([
                        Forms\Components\Repeater::make('barang_items')
                            ->label('Daftar Barang Keluar')
                            ->reactive()
                            ->schema([
                                Forms\Components\Grid::make(['sm' => 1, 'md' => 4])
                                    ->schema([
                                        Forms\Components\Select::make('barang_id')
                                            ->label('Pilih Barang')
                                            ->relationship('barang', 'nama_barang')
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->live()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Cari atau pilih barang...')
                                            ->prefixIcon('heroicon-o-magnifying-glass')
                                            ->columnSpan(1)
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state) {
                                                    $barang = Barang::find($state);
                                                    if ($barang) {
                                                        $set('nama_barang_display', $barang->nama_barang);
                                                        $set('stok_tersedia', $barang->jumlah_barang);
                                                        $set('kode_barang_display', $barang->kode_barang);

                                                        // Reset jumlah keluar
                                                        $set('jumlah_barang_keluar', null);

                                                        // Send notification about stock
                                                        if ($barang->jumlah_barang <= 5) {
                                                            Notification::make()
                                                                ->title('Peringatan Stok Rendah!')
                                                                ->body("Stok {$barang->nama_barang} tinggal {$barang->jumlah_barang} unit")
                                                                ->warning()
                                                                ->persistent()
                                                                ->send();
                                                        }
                                                    }
                                                }
                                            })
                                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nama_barang} (Stok: {$record->jumlah_barang})"),

                                        Forms\Components\TextInput::make('stok_tersedia')
                                            ->label('Stok Tersedia')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-o-archive-box')
                                            ->placeholder('0')
                                            ->columnSpan(1)
                                            ->extraAttributes(['class' => 'text-green-600 font-semibold']),

                                        Forms\Components\TextInput::make('jumlah_barang_keluar')
                                            ->label('Jumlah Keluar')
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->live()
                                            ->minValue(1)
                                            ->prefixIcon('heroicon-o-minus')
                                            ->placeholder('0')
                                            ->columnSpan(1)
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

                                        Forms\Components\TextInput::make('sisa_stok_display')
                                            ->label('Sisa Stok')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-o-calculator')
                                            ->placeholder('0')
                                            ->columnSpan(1)
                                            ->extraAttributes(['class' => 'text-blue-600 font-semibold']),

                                        // Hidden fields for display purposes
                                        Forms\Components\Hidden::make('nama_barang_display'),
                                        Forms\Components\Hidden::make('kode_barang_display'),
                                    ])
                            ])
                            ->itemLabel(
                                fn(array $state): ?string =>
                                isset($state['nama_barang_display']) ?
                                    $state['nama_barang_display'] . ' (' . ($state['jumlah_barang_keluar'] ?? '0') . ' unit keluar)' :
                                    'Barang Keluar'
                            )
                            ->collapsible()
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
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn($record) => 'Kode: ' . $record->barang->kode_barang ?? '-')
                    ->icon('heroicon-o-cube'),

                Tables\Columns\TextColumn::make('barang.kategori.nama_kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_barang_keluar')
                    ->label('Jumlah Keluar')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->weight(FontWeight::Bold)
                    ->color('danger')
                    ->suffix(' unit')
                    ->icon('heroicon-o-minus-circle'),

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
                    ->toggleable()
                    ->placeholder('Bukan dari project')
                    ->description('Khusus untuk status project')
                    ->icon('heroicon-o-folder'),

                Tables\Columns\TextColumn::make('pengajuan.user.name')
                    ->label('Pemohon Pengajuan')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Bukan dari pengajuan')
                    ->description('Dari pengajuan yang disetujui')
                    ->icon('heroicon-o-user-circle'),

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
                    ->limit(50)
                    ->toggleable()
                    ->placeholder('Tidak ada keterangan')
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

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
                    }),
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
