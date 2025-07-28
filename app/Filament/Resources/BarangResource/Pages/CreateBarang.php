<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;

class CreateBarang extends CreateRecord
{
    protected static string $resource = BarangResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Input barang';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Konversi field spesifikasi flat ke struktur JSON
        $spesifikasi = [];

        // Ambil semua field yang dimulai dengan 'spec_'
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'spec_')) {
                $specKey = str_replace('spec_', '', $key);
                if (!empty($value)) {
                    $spesifikasi[$specKey] = $value;
                }
                // Hapus field flat dari data utama
                unset($data[$key]);
            }
        }

        // Set spesifikasi ke data
        $data['spesifikasi'] = !empty($spesifikasi) ? $spesifikasi : null;

        return $data;
    }
}
