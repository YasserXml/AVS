<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManagerhrdmediaResource\Pages;
use App\Filament\Resources\ManagerhrdmediaResource\RelationManagers;
use App\Models\Managerhrdmedia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManagerhrdmediaResource extends Resource
{
    protected static ?string $model = Managerhrdmedia::class;

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
            'index' => Pages\ListManagerhrdmedia::route('/'),
            'create' => Pages\CreateManagerhrdmedia::route('/create'),
            'edit' => Pages\EditManagerhrdmedia::route('/{record}/edit'),
        ];
    }
}
