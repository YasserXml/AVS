<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ElektromediaResource\Pages;
use App\Filament\Resources\ElektromediaResource\RelationManagers;
use App\Models\Elektromedia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ElektromediaResource extends Resource
{
    protected static ?string $model = Elektromedia::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            'index' => Pages\ListElektromedia::route('/'),
            'create' => Pages\CreateElektromedia::route('/create'),
            'edit' => Pages\EditElektromedia::route('/{record}/edit'),
        ];
    }
}
