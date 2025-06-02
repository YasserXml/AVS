<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengembalianResource\Pages;
use App\Filament\Resources\PengembalianResource\RelationManagers;
use App\Models\Barangkeluar;
use App\Models\Pengajuan;
use App\Models\Pengembalian;
use App\Services\PengembalianService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PengembalianResource extends Resource
{
    protected static ?string $model = Pengembalian::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Pengembalian Barang';

    protected static ?int $navigationSort = 9;

    protected static ?string $pluralLabel = 'Pengembalian Barang';

    protected static ?string $slug = 'pengembalian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengembalian')
                    ->schema([
                        Forms\Components\Select::make('pengajuan_id')
                            ->label('Pengajuan')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $pengajuan = Pengajuan::find($state);
                                    if ($pengajuan) {
                                        $set('barang_id', $pengajuan->barang_id);
                                        $set('max_jumlah', $pengajuan->sisaBisaDikembalikan());
                                    }
                                }
                            }),

                        Forms\Components\Select::make('barang_keluar_id')
                            ->label('Barang Keluar')
                            ->options(function (callable $get) {
                                $pengajuanId = $get('pengajuan_id');
                                if (!$pengajuanId) return [];
                                
                                return Barangkeluar::where('pengajuan_id', $pengajuanId)
                                    ->with('barang')
                                    ->get()
                                    ->mapWithKeys(function ($barangKeluar) {
                                        return [
                                            $barangKeluar->id => "#{$barangKeluar->id} - {$barangKeluar->barang->nama_barang} ({$barangKeluar->jumlah_barang_keluar} unit)"
                                        ];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->live(),

                        Forms\Components\TextInput::make('jumlah_dikembalikan')
                            ->label('Jumlah Dikembalikan')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(function (callable $get) {
                                return $get('max_jumlah') ?? 999;
                            })
                            ->helperText(function (callable $get) {
                                $max = $get('max_jumlah');
                                return $max ? "Maksimal: {$max} unit" : '';
                            }),

                        Forms\Components\DatePicker::make('tanggal_pengembalian')
                            ->label('Tanggal Pengembalian')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\Select::make('kondisi')
                            ->label('Kondisi Barang')
                            ->options([
                                'baik' => 'Baik',
                                'rusak' => 'Rusak',
                                'hilang' => 'Hilang',
                            ])
                            ->placeholder('Kondisi barang yang dikembalikan')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->placeholder('Berikan keterangan terkait pengembalian barang (opsional)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Approval')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('pending')
                            ->disabled(fn ($operation) => $operation === 'create'),

                        Forms\Components\Textarea::make('reject_reason')
                            ->label('Alasan Penolakan')
                            ->visible(fn (callable $get) => $get('status') === 'rejected')
                            ->maxLength(65535),
                    ])
                    ->columns(1)
                    ->visible(fn ($operation) => $operation === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pengajuan.id')
                    ->label('ID Pengajuan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengaju')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_dikembalikan')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_pengembalian')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('kondisi')
                    ->label('Kondisi')
                    ->colors([
                        'success' => 'baik',
                        'warning' => 'rusak',
                        'danger' => 'hilang',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('kondisi')
                    ->label('Kondisi')
                    ->options([
                        'baik' => 'Baik',
                        'rusak' => 'Rusak',
                        'hilang' => 'Hilang',
                    ]),

                Tables\Filters\Filter::make('tanggal_pengembalian')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pengembalian', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],  
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pengembalian', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Pengembalian $record) => $record->status === 'pending')
                    ->action(function (Pengembalian $record) {
                        $service = new PengembalianService();
                        $result = $service->approvePengembalian($record->id);
                        
                        if ($result['success']) {
                            Notification::make()
                                ->title('Berhasil')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Pengembalian $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function (Pengembalian $record, array $data) {
                        $service = new PengembalianService();
                        $result = $service->rejectPengembalian($record->id, $data['reason']);
                        
                        if ($result['success']) {
                            Notification::make()
                                ->title('Berhasil')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $service = new PengembalianService();
                            $ids = $records->where('status', 'pending')->pluck('id')->toArray();
                            
                            if (empty($ids)) {
                                Notification::make()
                                    ->title('Tidak Ada Data')
                                    ->body('Tidak ada pengembalian yang bisa disetujui')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            
                            $result = $service->bulkApprovePengembalian($ids);
                            
                            if ($result['success']) {
                                Notification::make()
                                    ->title('Berhasil')
                                    ->body($result['message'])
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Gagal')
                                    ->body($result['message'])
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Jika bukan admin, hanya tampilkan pengembalian milik user yang login
                if (!filament()->auth()->user()->hasRole('admin')) {
                    $query->where('user_id', Auth::id());
                }
                
                return $query->with(['pengajuan.barang', 'barang', 'user']);
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Pengembalian')
                    ->schema([
                    TextEntry::make('id')
                            ->label('ID Pengembalian'),
                            
                    TextEntry::make('pengajuan.id')
                            ->label('ID Pengajuan'),
                            
                    TextEntry::make('barang.nama_barang')
                            ->label('Nama Barang'),
                            
                    TextEntry::make('barang.serial_number')
                            ->label('Serial Number'),
                            
                    TextEntry::make('user.name')
                            ->label('Pengaju'),
                            
                    TextEntry::make('jumlah_dikembalikan')
                            ->label('Jumlah Dikembalikan')
                            ->suffix(' unit'),
                    TextEntry::make('tanggal_pengembalian')
                            ->label('Tanggal Pengembalian')
                            ->date(),
                            
                    TextEntry::make('kondisi')
                            ->label('Kondisi Barang')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'baik' => 'success',
                                'rusak' => 'warning',
                                'hilang' => 'danger',
                            }),
                            
                    TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Status Approval')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            }),
                            
                        TextEntry::make('approvedBy.name')
                            ->label('Disetujui Oleh')
                            ->visible(fn ($record) => $record->status === 'approved'),
                            
                        TextEntry::make('approved_at')
                            ->label('Tanggal Disetujui')
                            ->dateTime()
                            ->visible(fn ($record) => $record->status === 'approved'),
                            
                        TextEntry::make('rejectedBy.name')
                            ->label('Ditolak Oleh')
                            ->visible(fn ($record) => $record->status === 'rejected'),
                            
                        TextEntry::make('rejected_at')
                            ->label('Tanggal Ditolak')
                            ->dateTime()
                            ->visible(fn ($record) => $record->status === 'rejected'),
                            
                        TextEntry::make('reject_reason')
                            ->label('Alasan Penolakan')
                            ->visible(fn ($record) => $record->status === 'rejected')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Informasi Pengajuan Terkait')
                    ->schema([
                       TextEntry::make('pengajuan.Jumlah_barang_diajukan')
                            ->label('Jumlah Diajukan')
                            ->suffix(' unit'),
                            
                        TextEntry::make('pengajuan.status_barang')
                            ->label('Status Barang'),
                            
                        TextEntry::make('pengajuan.nama_project')
                            ->label('Nama Project')
                            ->visible(fn ($record) => !empty($record->pengajuan->nama_project)),
                            
                        TextEntry::make('sisa_bisa_dikembalikan')
                            ->label('Sisa Bisa Dikembalikan')
                            ->suffix(' unit')
                            ->state(fn ($record) => $record->pengajuan->sisaBisaDikembalikan()),
                    ])
                    ->columns(2),
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
            'index' => Pages\ListPengembalians::route('/'),
            'create' => Pages\CreatePengembalian::route('/create'),
            // 'view' => Pages\ViewPengembalian::route('/{record}'),
            'edit' => Pages\EditPengembalian::route('/{record}/edit'),
        ];
    }
   
}
