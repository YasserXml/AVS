<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListPengajuans extends ListRecords
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->icon('heroicon-o-plus-circle')
                ->label('Buat Pengajuan')
                ->iconPosition(IconPosition::Before)
                ->color('success')
                ->tooltip('Klik untuk membuat pengajuan')
                ->size('lg')
                ->extraAttributes([
                    'class' => 'font-semibold shadow-lg hover:shadow-xl transition-all duration-200'
                ]),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Pengajuan')
                ->icon('heroicon-o-clipboard-document-list')
                ->badgeColor('info')
                ->badge(fn () => $this->getModel()::query()->count()),
            
            'pending' => Tab::make('Menunggu Persetujuan')
                ->icon('heroicon-o-clock')
                ->badge(fn () => $this->getModel()::query()->where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            
            'approved' => Tab::make('Disetujui')
                ->icon('heroicon-o-check-circle')
                ->badge(fn () => $this->getModel()::query()->where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),
            
            'rejected' => Tab::make('Ditolak')
                ->icon('heroicon-o-x-circle')
                ->badge(fn () => $this->getModel()::query()->where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),
        ];
    }

     public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Pengajuan Barang</span>
            </div>
        ');
    }
}
