<?php

namespace App\Filament\Resources\PengajuanoprasionalResource\Pages;

use App\Filament\Resources\PengajuanoprasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CreatePengajuanoprasional extends CreateRecord
{
    protected static string $resource = PengajuanoprasionalResource::class;

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Buat Pengajuan Barang Oprasional</span>
            </div>
        ');
    }
}
