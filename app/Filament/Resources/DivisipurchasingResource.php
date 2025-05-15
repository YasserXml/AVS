<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisipurchasingResource\Pages;
use App\Filament\Resources\DivisipurchasingResource\RelationManagers;
use App\Models\Divisipurchasing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DivisipurchasingResource extends Resource
{
    protected static ?string $model = Divisipurchasing::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Arsip';

    protected static ?string $navigationLabel = 'Divisi Purchasing';

    protected static ?int $navigationSort = 13;

    protected static ?string $label = 'Divisi Purchasing';

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
            'index' => Pages\ListDivisipurchasings::route('/'),
            'create' => Pages\CreateDivisipurchasing::route('/create'),
            'edit' => Pages\EditDivisipurchasing::route('/{record}/edit'),
        ];
    }

    
}
