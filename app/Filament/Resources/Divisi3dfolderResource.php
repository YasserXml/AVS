<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Divisi3dfolderResource\Pages;
use App\Filament\Resources\Divisi3dfolderResource\RelationManagers;
use App\Models\Divisi3dfolder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Divisi3dfolderResource extends Resource
{
    protected static ?string $model = Divisi3dfolder::class;

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
            'index' => Pages\ListDivisi3dfolders::route('/'),
            'create' => Pages\CreateDivisi3dfolder::route('/create'),
            'edit' => Pages\EditDivisi3dfolder::route('/{record}/edit'),
        ];
    }
}
