<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Divisi3dmediaResource\Pages;
use App\Filament\Resources\Divisi3dmediaResource\RelationManagers;
use App\Models\Divisi3dmedia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Divisi3dmediaResource extends Resource
{
    protected static ?string $model = Divisi3dmedia::class;

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
            'index' => Pages\ListDivisi3dmedia::route('/'),
            'create' => Pages\CreateDivisi3dmedia::route('/create'),
            'edit' => Pages\EditDivisi3dmedia::route('/{record}/edit'),
        ];
    }
}
