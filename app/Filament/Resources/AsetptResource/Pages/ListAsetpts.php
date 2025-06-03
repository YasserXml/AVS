<?php

namespace App\Filament\Resources\AsetptResource\Pages;

use App\Filament\Resources\AsetptResource;
use App\Models\Asetpt;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListAsetpts extends ListRecords
{
    protected static string $resource = AsetptResource::class;

   protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Aset Baru')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->badge(fn () => Asetpt::count()),
                
            'stok' => Tab::make('Stok')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'stok'))
                ->badge(fn () =>Asetpt::where('status', 'stok')->count())
                ->badgeColor('success'),
                
            'pengembalian' => Tab::make('Pengembalian')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pengembalian'))
                ->badge(fn () => Asetpt::where('status', 'pengembalian')->count())
                ->badgeColor('warning'),
                
            'baik' => Tab::make('Kondisi Baik')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kondisi', 'baik'))
                ->badge(fn () =>Asetpt::where('kondisi', 'baik')->count())
                ->badgeColor('success'),
                
            'rusak' => Tab::make('Kondisi Rusak')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kondisi', 'rusak'))
                ->badge(fn () => Asetpt::where('kondisi', 'rusak')->count())
                ->badgeColor('danger'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">AsetPT</span>
            </div>
        ');
    }
}
