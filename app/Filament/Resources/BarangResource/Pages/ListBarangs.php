<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use App\Models\Barang;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\HtmlString;
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
                ->iconPosition(IconPosition::Before)
                ->color('success')
                ->size('lg')
                ->tooltip('Tambah barang baru ke inventory'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Barang')
                ->badge(fn () => $this->getModel()::count())
                ->badgeColor('info')
                ->icon('heroicon-o-clipboard-document-list'),
            
            'stok_tersedia' => Tab::make('Stok Tersedia')
                ->badge(fn () => $this->getModel()::where('jumlah_barang', '>', 0)->count())
                ->badgeColor('success')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jumlah_barang', '>', 0)),
            
            'stok_menipis' => Tab::make('Stok Menipis')
                ->badge(fn () => $this->getModel()::where('jumlah_barang', '>', 0)->where('jumlah_barang', '<', 10)->count())
                ->badgeColor('warning')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jumlah_barang', '>', 0)->where('jumlah_barang', '<', 10)),
            
            'stok_kosong' => Tab::make('Stok Kosong')
                ->badge(fn () => $this->getModel()::where('jumlah_barang', 0)->count())
                ->badgeColor('danger')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jumlah_barang', 0)),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Ketersediaan Barang</span>
            </div>
        ');
    }
    
    public function getBreadcrumbs(): array
    {
        return [
            url('/') => 'Dashboard',
            url($this->getResource()::getUrl()) => 'Inventaris',
            'Daftar Barang',
        ];
    }
    
    // protected function getFooter(): View|Htmlable|null
    // {
    //     return FilamentView::make('filament.pages.barang-footer')
    //         ->with([
    //             'totalBarang' => Barang::count(),
    //             'lastUpdated' => Barang::latest('updated_at')->first()?->updated_at?->diffForHumans() ?? 'Belum ada update',
    //         ]);
    // }
}