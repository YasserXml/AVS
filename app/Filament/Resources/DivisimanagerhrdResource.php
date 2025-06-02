<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisimanagerhrdResource\Pages;
use App\Filament\Resources\DivisimanagerhrdResource\RelationManagers;
use App\Models\Divisimanagerhrd;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DivisimanagerhrdResource extends Resource
{
    protected static ?string $model = Divisimanagerhrd::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Arsip';

    protected static ?string $navigationLabel = 'Divisi Manager HRD';

    protected static ?int $navigationSort = 9;

    protected static ?string $label = 'Divisi Manager HRD';

    protected static ?string $slug = 'divisi-manager-hrd';

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
            'index' => Pages\ListDivisimanagerhrds::route('/'),
            'create' => Pages\CreateDivisimanagerhrd::route('/create'),
            'edit' => Pages\EditDivisimanagerhrd::route('/{record}/edit'),
        ];
    }

   
}
