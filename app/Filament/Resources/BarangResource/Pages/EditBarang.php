<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditBarang extends EditRecord
{
    protected static string $resource = BarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Edit data barang';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['spesifikasi']) && is_array($data['spesifikasi'])) {
            foreach ($data['spesifikasi'] as $key => $value) {
                $data['spec_' . $key] = $value;
            }
        }
        return $data;
    }

    // Konversi field flat kembali ke JSON sebelum disimpan
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $spesifikasi = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'spec_')) {
                $specKey = str_replace('spec_', '', $key);
                if (!empty($value)) {
                    $spesifikasi[$specKey] = $value;
                }
                unset($data[$key]);
            }
        }

        $data['spesifikasi'] = !empty($spesifikasi) ? $spesifikasi : null;
        return $data;
    }
}
