<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

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
                ->size('lg')
                ->extraAttributes([
                    'class' => 'font-semibold shadow-lg hover:shadow-xl transition-all duration-200'
                ]),
        ];
    }
    
     public function getTabs(): array
    {
        $allCount = static::getResource()::getEloquentQuery()->count();
        $operationalCount = static::getResource()::getEloquentQuery()
            ->where('status', 'oprasional_kantor')
            ->count();
        $projectCount = static::getResource()::getEloquentQuery()
            ->where('status', 'project')
            ->count();

        return [
            'all' => Tab::make('Semua')
                ->label("Semua ({$allCount})")
                ->icon('heroicon-o-squares-2x2')
                ->badge($allCount)
                ->badgeColor('primary'),
            
            'oprasional' => Tab::make('Operasional')
                ->label("Operasional ({$operationalCount})")
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'oprasional_kantor'))
                ->badge($operationalCount)
                ->badgeColor('success'),
            
            'project' => Tab::make('Project')
                ->label("Project ({$projectCount})")
                ->icon('heroicon-o-building-library')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'project'))
                ->badge($projectCount)
                ->badgeColor('warning'),
        ];
    }

     public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Barang Masuk</span>
            </div>
        ');
    }
}
