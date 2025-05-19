<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisipmoResource\Pages;
use App\Filament\Resources\DivisipmoResource\RelationManagers;
use App\Models\Divisipmo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DivisipmoResource extends Resource
{
    protected static ?string $model = Divisipmo::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Arsip';

    protected static ?string $navigationLabel = 'Divisi PMO';

    protected static ?int $navigationSort = 19;

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
            'index' => Pages\ListDivisipmos::route('/'),
            'create' => Pages\CreateDivisipmo::route('/create'),
            'edit' => Pages\EditDivisipmo::route('/{record}/edit'),
        ];
    }
}
