<?php

namespace App\Observers;

use App\Models\Barang;
use Illuminate\Support\Facades\Cache;

class BarangObserver
{
    /**
     * Handle the Barang "created" event.
     */
    public function created(Barang $barang): void
    {
        $this->clearKategoriCache($barang);
    }

    /**
     * Handle the Barang "updated" event.
     */
    public function updated(Barang $barang): void
    {
        $this->clearKategoriCache($barang);

        // Jika kategori berubah, clear cache untuk kategori lama juga
        if ($barang->isDirty('kategori_id')) {
            $originalKategoriId = $barang->getOriginal('kategori_id');
            if ($originalKategoriId) {
                Cache::forget("kategori_stats_{$originalKategoriId}");
            }
        }
    }

    /**
     * Handle the Barang "deleted" event.
     */
    public function deleted(Barang $barang): void
    {
        $this->clearKategoriCache($barang);
    }

    /**
     * Handle the Barang "restored" event.
     */
    public function restored(Barang $barang): void
    {
        $this->clearKategoriCache($barang);
    }

    /**
     * Handle the Barang "force deleted" event.
     */
    public function forceDeleted(Barang $barang): void
    {
        $this->clearKategoriCache($barang);
    }

    /**
     * Clear cache untuk kategori terkait
     */
    private function clearKategoriCache(Barang $barang): void
    {
        if ($barang->kategori_id) {
            Cache::forget("kategori_stats_{$barang->kategori_id}");
        }
    }
}
