<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManagerhrdfolderResource\Pages;
use App\Filament\Resources\ManagerhrdfolderResource\RelationManagers;
use App\Models\Managerhrdfolder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManagerhrdfolderResource extends Resource
{
    protected static ?string $model = Managerhrdfolder::class;

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
            'index' => Pages\ListManagerhrdfolders::route('/'),
            'create' => Pages\CreateManagerhrdfolder::route('/create'),
            'edit' => Pages\EditManagerhrdfolder::route('/{record}/edit'),
        ];
    }
}
