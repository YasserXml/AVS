<?php

namespace App\Filament\Resources\PengajuanoprasionalResource\Pages;

use App\Filament\Resources\PengajuanoprasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListPengajuanoprasionals extends ListRecords
{
    protected static string $resource = PengajuanoprasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->label('Buat Pengajuan')
             ->icon('heroicon-o-plus')
             ->iconPosition('before'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Pengajuan Oprasional</span>
            </div>
        ');
    }
}
