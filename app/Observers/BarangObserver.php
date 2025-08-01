<?php

namespace App\Observers;

use App\Models\Barang;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BarangObserver
{
    /**
     * Handle the Barang "creating" event.
     */
    public function creating(Barang $barang): void
    {
        $this->processSpesifikasi($barang);
    }

    /**
     * Handle the Barang "updating" event.
     */
    public function updating(Barang $barang): void
    {
        $this->processSpesifikasi($barang);
    }

    /**
     * Process spesifikasi data from flat fields to JSON
     */
    private function processSpesifikasi(Barang $barang): void
    {
        $spesifikasi = [];

        // Get all attributes
        $attributes = $barang->getAttributes();

        // Extract spec_ fields
        foreach ($attributes as $key => $value) {
            if (strpos($key, 'spec_') === 0 && !empty($value)) {
                $spesifikasi[$key] = $value;
                // Remove from main attributes since we'll store in JSON
                unset($barang->{$key});
            }
        }

        // Only set spesifikasi if we have spec data
        if (!empty($spesifikasi)) {
            $barang->setAttribute('spesifikasi', $spesifikasi);
        }

        Log::info('Processed spesifikasi for barang', [
            'barang_id' => $barang->id ?? 'new',
            'serial_number' => $barang->serial_number,
            'spesifikasi' => $spesifikasi
        ]);
    }
}
