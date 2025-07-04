<?php

namespace App\Filament\Resources\PengajuanoprasionalResource\Pages;

use App\Filament\Resources\PengajuanoprasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePengajuanoprasional extends CreateRecord
{
    protected static string $resource = PengajuanoprasionalResource::class;

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
