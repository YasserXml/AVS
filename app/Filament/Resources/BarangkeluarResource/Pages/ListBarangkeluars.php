<?php

namespace App\Filament\Resources\BarangkeluarResource\Pages;

use App\Filament\Resources\BarangkeluarResource;
use App\Models\Barangkeluar;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListBarangkeluars extends ListRecords
{
    protected static string $resource = BarangkeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Barang Keluar')
                ->icon('heroicon-o-plus')
                ->color('success'),
        ];
    }

   public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Barang Keluar</span>
            </div>
        ');
    }

    
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Data')
                ->icon('heroicon-o-rectangle-stack')
                ->badge(BarangKeluar::count()),

            'manual' => Tab::make('Input Manual')
                ->icon('heroicon-o-pencil-square')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sumber', 'manual'))
                ->badge(BarangKeluar::where('sumber', 'manual')->count())
                ->badgeColor('primary'),

            'pengajuan' => Tab::make('Dari Pengajuan')
                ->icon('heroicon-o-clipboard-document-check')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sumber', 'pengajuan'))
                ->badge(BarangKeluar::where('sumber', 'pengajuan')->count())
                ->badgeColor('success'),

            'operasional' => Tab::make('Operasional Kantor')
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'oprasional_kantor'))
                ->badge(BarangKeluar::where('status', 'oprasional_kantor')->count())
                ->badgeColor('info'),

            'project' => Tab::make('Project')
                ->icon('heroicon-o-briefcase')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'project'))
                ->badge(BarangKeluar::where('status', 'project')->count())
                ->badgeColor('warning'),
        ];
    }
    
}
