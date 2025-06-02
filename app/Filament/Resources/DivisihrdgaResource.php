<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisihrdgaResource\Pages;
use App\Filament\Resources\DivisihrdgaResource\RelationManagers;
use App\Models\Divisihrdga;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DivisihrdgaResource extends Resource
{
    protected static ?string $model = Divisihrdga::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Arsip';

    protected static ?string $navigationLabel = 'Divisi HRD & GA';

    protected static ?int $navigationSort = 10;

    protected static ?string $label = 'Divisi HRD & GA';

    protected static ?string $slug = 'divisi-hrd-ga';

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
            'index' => Pages\ListDivisihrdgas::route('/'),
            'create' => Pages\CreateDivisihrdga::route('/create'),
            'edit' => Pages\EditDivisihrdga::route('/{record}/edit'),
        ];
    }

   
}
