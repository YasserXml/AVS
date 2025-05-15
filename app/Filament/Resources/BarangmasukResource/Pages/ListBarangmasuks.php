<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconPosition;

class ListBarangmasuks extends ListRecords
{
    protected static string $resource = BarangmasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label('Tambah Barang Masuk')
                ->iconPosition(IconPosition::Before)
                ->color('success')
                ->size('lg'),
        ];
    }

    public function getTitle(): string
    {
        return "Barang Masuk";
    }
}
