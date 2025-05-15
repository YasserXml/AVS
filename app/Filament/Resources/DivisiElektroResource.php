<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisiElektroResource\Pages;
use App\Filament\Resources\DivisiElektroResource\RelationManagers;
use App\Models\DivisiElektro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DivisiElektroResource extends Resource
{
    protected static ?string $model = DivisiElektro::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Arsip';

    protected static ?string $navigationLabel = 'Divisi Elektro';

    protected static ?int $navigationSort = 14;

    protected static ?string $label = 'Divisi Elektro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListDivisiElektros::route('/'),
            'create' => Pages\CreateDivisiElektro::route('/create'),
            'edit' => Pages\EditDivisiElektro::route('/{record}/edit'),
        ];
    }

    
}
