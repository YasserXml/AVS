<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;

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

    public function getTitle(): Htmlable|string
    {
        return 'Daftar Barang Masuk';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->label('Semua Barang Masuk')
                ->icon('heroicon-o-squares-2x2'),
            
            'oprasional' => Tab::make('Operasional')
                ->label('Operasional Kantor')
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'oprasional_kantor')),
            
            'project' => Tab::make('Project')
                ->label('Project')
                ->icon('heroicon-o-building-library')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'project')),
        ];
    }
}
