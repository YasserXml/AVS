<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarangmasuks extends ListRecords
{
    protected static string $resource = BarangmasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Input Barang Masuk')
            ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return "Barang Masuk";
    }
}
