<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MekanikfolderResource\Pages;
use App\Filament\Resources\MekanikfolderResource\RelationManagers;
use App\Models\Mekanikfolder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MekanikfolderResource extends Resource
{
    protected static ?string $model = Mekanikfolder::class;

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
            'index' => Pages\ListMekanikfolders::route('/'),
            'create' => Pages\CreateMekanikfolder::route('/create'),
            'edit' => Pages\EditMekanikfolder::route('/{record}/edit'),
        ];
    }
}
