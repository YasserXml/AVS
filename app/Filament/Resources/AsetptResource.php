<?php

namespace App\Filament\Resources;

use App\Exports\AsetExporter;
use App\Filament\Resources\AsetptResource\Pages;
use App\Filament\Resources\AsetptResource\RelationManagers;
use App\Models\Asetpt;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AsetptResource extends Resource
{
    protected static ?string $model = Asetpt::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $activeNavigationIcon = 'heroicon-s-building-library';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Aset PT';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'inventory/aset-pt';

     public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Aset')
                    ->description('Masukkan detail informasi aset')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama barang')
                            ->columnSpan(2),
                            
                        Forms\Components\TextInput::make('qty')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->suffix('unit')
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                    
                Forms\Components\Section::make('Detail Tambahan')
                    ->description('Informasi tambahan tentang aset')
                    ->schema([
                        Forms\Components\TextInput::make('brand')
                            ->label('Merek/Brand')
                            ->maxLength(255)
                            ->placeholder('Masukkan merek barang'),
                            
                        Forms\Components\TextInput::make('pic')
                            ->label('Penanggung Jawab (PIC)/ Lokasi')
                            ->maxLength(255)
                            ->placeholder('Nama penanggung jawab/ Lokasi barang'),
                            
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'stok' => 'Stok',
                                'pengembalian' => 'Pengembalian',
                            ])
                            ->default('stok')
                            ->required()
                            ->native(false),
                            
                        Forms\Components\Select::make('kondisi')
                            ->label('Kondisi')
                            ->options([
                                'baik' => 'Baik',
                                'rusak' => 'Rusak',
                            ])
                            ->default('baik')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('qty')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' unit'),
                    
                Tables\Columns\TextColumn::make('brand')
                    ->label('Merek')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('Tidak ada merek'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'stok',
                        'warning' => 'pengembalian',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'stok' => 'Stok',
                        'pengembalian' => 'Pengembalian',
                        default => $state,
                    }),
                    
                Tables\Columns\BadgeColumn::make('kondisi')
                    ->label('Kondisi')
                    ->colors([
                        'success' => 'baik',
                        'danger' => 'rusak',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'baik' => 'Baik',
                        'rusak' => 'Rusak',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('pic')
                    ->label('PIC/Lokasi')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('Belum ditentukan'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'stok' => 'Stok',
                        'pengembalian' => 'Pengembalian',
                    ])
                    ->native(false),
                    
                SelectFilter::make('kondisi')
                    ->label('Kondisi')
                    ->options([
                        'baik' => 'Baik',
                        'rusak' => 'Rusak',
                    ])
                    ->native(false),
                    
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->native(false),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->format('d/m/Y');
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
                
                Tables\Filters\TrashedFilter::make()
                    ->label('Data Terhapus')
                ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Pulihkan'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Permanen'),
                ])
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export Semua Data')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (){
                        return (new AsetExporter())->export();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export_selected')
                    ->label('Export Data Terpilih')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Collection $records){
                        return(new AsetExporter())->export();
                    }),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen Terpilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
              Section::make('Informasi Aset')
                    ->schema([
                       TextEntry::make('tanggal')
                            ->label('Tanggal')
                            ->date('d F Y')
                            ->placeholder('Tidak ada tanggal'),
                            
                        TextEntry::make('nama_barang')
                            ->label('Nama Barang')
                            ->weight(FontWeight::SemiBold),
                            
                        TextEntry::make('qty')
                            ->label('Jumlah')
                            ->suffix(' unit'),
                            
                        TextEntry::make('brand')
                            ->label('Merek/Brand')
                            ->placeholder('Tidak ada merek'),
                    ])
                    ->columns(2),
                    
              Section::make('Status & Kondisi')
                    ->schema([
                     TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'stok' => 'success',
                                'pengembalian' => 'warning',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'stok' => 'Stok',
                                'pengembalian' => 'Pengembalian',
                                default => $state,
                            }),
                            
                        TextEntry::make('kondisi')
                            ->label('Kondisi')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'baik' => 'success',
                                'rusak' => 'danger',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'baik' => 'Baik',
                                'rusak' => 'Rusak',
                                default => $state,
                            }),
                            
                     TextEntry::make('pic')
                            ->label('Penanggung Jawab (PIC)')
                            ->placeholder('Belum ditentukan'),
                    ])
                    ->columns(3),
                    
                Section::make('Informasi Sistem')
                    ->schema([
                       TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y, H:i:s'),
                            
                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d F Y, H:i:s'),
                            
                        TextEntry::make('deleted_at')
                            ->label('Dihapus Pada')
                            ->dateTime('d F Y, H:i:s')
                            ->placeholder('Tidak dihapus'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsetpts::route('/'),
            'create' => Pages\CreateAsetpt::route('/create'),
            'edit' => Pages\EditAsetpt::route('/{record}/edit'),
        ];
    }
}
