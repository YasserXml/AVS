<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Number;
use Illuminate\View\View;

class ListBarangs extends ListRecords
{
    protected static string $resource = BarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
               ->label('Tambah Barang')
                ->icon('heroicon-o-plus-circle')
                ->iconPosition(IconPosition::Before),
        ];
    }

     public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Barang')
                ->badge(fn () => $this->getModel()::count()),
            
            'stok_tersedia' => Tab::make('Stok Tersedia')
                ->badge(fn () => $this->getModel()::where('jumlah_barang', '>', 0)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jumlah_barang', '>', 0)),
            
            'stok_menipis' => Tab::make('Stok Menipis')
                ->badge(fn () => $this->getModel()::where('jumlah_barang', '>', 0)->where('jumlah_barang', '<', 10)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jumlah_barang', '>', 0)->where('jumlah_barang', '<', 10)),
            
            'stok_kosong' => Tab::make('Stok Kosong')
                ->badge(fn () => $this->getModel()::where('jumlah_barang', 0)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jumlah_barang', 0)),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Ketersediaan Barang';
    }

     public function getFooter(): ?View
    {
        $totalNilaiInventaris = $this->getModel()::query()
            ->selectRaw('SUM(jumlah_barang * harga_barang) as total_nilai')
            ->value('total_nilai');

        $totalJenis = $this->getModel()::count();
        $totalStok = $this->getModel()::sum('jumlah_barang');

        return view('sum.barang-sum', [
            'totalNilaiInventaris' => $totalNilaiInventaris,
            'totalJenis' => $totalJenis,
            'totalStok' => $totalStok,
        ]);
    }
}
