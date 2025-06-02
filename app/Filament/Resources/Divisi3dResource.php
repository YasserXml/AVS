<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Divisi3dResource\Pages;
use App\Filament\Resources\Divisi3dResource\RelationManagers;
use App\Models\Divisi3d;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Divisi3dResource extends Resource
{
    protected static ?string $model = Divisi3d::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Arsip';

    protected static ?string $navigationLabel = 'Divisi 3D';

    protected static ?int $navigationSort = 17;

    protected static ?string $label = 'Divisi 3D';

    protected static ?string $slug = 'divisi-3d';

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
            'index' => Pages\ListDivisi3ds::route('/'),
            'create' => Pages\CreateDivisi3d::route('/create'),
            'edit' => Pages\EditDivisi3d::route('/{record}/edit'),
        ];
    }

    
}
