<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubkategoriResource\Pages;
use App\Filament\Resources\SubkategoriResource\RelationManagers;
use App\Models\Subkategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubkategoriResource extends Resource
{
    protected static ?string $model = Subkategori::class;

     protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $activeNavigationIcon = 'heroicon-s-bookmark';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Sub Kategori Barang';

    protected static ?string $label = 'Subkategori Barang';

    protected static ?string $pluralLabel = 'Subkategori Barang';

    protected static ?string $slug = 'inventory/sub-kategori';

    // protected static ?string $modelLabel = 'Kategori Barang';

    protected static ?string $pluralModelLabel = 'Sub Kategori Barang';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Subkategori')
                    ->description('Masukkan informasi subkategori')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Forms\Components\Select::make('kategori_id')
                            ->label('Kategori Utama')
                            ->relationship('kategori', 'nama_kategori')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih kategori utama')
                            ->prefixIcon('heroicon-m-folder')
                            ->columnSpan(['default' => 2, 'md' => 1]),

                        Forms\Components\TextInput::make('nama_subkategori')
                            ->label('Nama Subkategori')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama subkategori')
                            ->prefixIcon('heroicon-m-tag')
                            ->columnSpan(['default' => 2, 'md' => 1])
                            ->autocapitalize(),
                    ])
                    ->columns(['default' => 1, 'md' => 2])
                    ->collapsible()
                    ->persistCollapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori Utama')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nama_subkategori')
                    ->label('Nama Subkategori')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('barang_count')
                    ->label('Jumlah Barang')
                    ->counts('barang')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListSubkategoris::route('/'),
            'create' => Pages\CreateSubkategori::route('/create'),
            'edit' => Pages\EditSubkategori::route('/{record}/edit'),
        ];
    }
}
