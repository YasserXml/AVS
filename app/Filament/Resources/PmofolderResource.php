<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PmofolderResource\Pages;
use App\Filament\Resources\PmofolderResource\RelationManagers;
use App\Models\Pmofolder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PmofolderResource extends Resource
{
    protected static ?string $model = Pmofolder::class;

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
            'index' => Pages\ListPmofolders::route('/'),
            'create' => Pages\CreatePmofolder::route('/create'),
            'edit' => Pages\EditPmofolder::route('/{record}/edit'),
        ];
    }
}
