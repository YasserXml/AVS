<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Support\Collection;

class BarangHelperService
{
    /**
     * Mendapatkan daftar barang yang sudah ada dengan grouping berdasarkan nama
     */
    public static function getExistingBarangOptions(): array
    {
        return Barang::with(['kategori'])
            ->selectRaw('MIN(id) as id, nama_barang, kategori_id, SUM(jumlah_barang) as total_stock')
            ->groupBy(['nama_barang', 'kategori_id'])
            ->having('total_stock', '>', 0)
            ->get()
            ->mapWithKeys(function ($barang) {
                $kategoriNama = $barang->kategori->nama_kategori ?? 'Tidak ada kategori';
                return [
                    $barang->id => "{$barang->nama_barang} - {$kategoriNama} (Total: {$barang->total_stock} unit)"
                ];
            })
            ->toArray();
    }

    /**
     * Mendapatkan detail barang berdasarkan nama (mengambil yang pertama sebagai template)
     */
    public static function getBarangTemplateByName(string $namaBarang): ?Barang
    {
        return Barang::with('kategori')
            ->where('nama_barang', $namaBarang)
            ->first();
    }

    /**
     * Generate kode barang baru yang unik
     */
    public static function generateKodeBarang(): int
    {
        $lastKode = Barang::max('kode_barang');
        return ($lastKode ?? 0) + 1;
    }

    /**
     * Cek apakah serial number sudah ada
     */
    public static function isSerialNumberExists(string $serialNumber, ?int $ignoreId = null): bool
    {
        $query = Barang::where('serial_number', $serialNumber);
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        
        return $query->exists();
    }

    /**
     * Mendapatkan semua barang dengan nama yang sama
     */
    public static function getBarangByName(string $namaBarang): Collection
    {
        return Barang::with(['kategori'])
            ->where('nama_barang', $namaBarang)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mendapatkan total stock berdasarkan nama barang
     */
    public static function getTotalStockByName(string $namaBarang): int
    {
        return Barang::where('nama_barang', $namaBarang)->sum('jumlah_barang');
    }

    /**
     * Mendapatkan jumlah unit (record terpisah) berdasarkan nama barang
     */
    public static function getUnitCountByName(string $namaBarang): int
    {
        return Barang::where('nama_barang', $namaBarang)->count();
    }

    /**
     * Format spesifikasi untuk ditampilkan
     */
    public static function formatSpesifikasi(?array $spesifikasi): string
    {
        if (!$spesifikasi || empty($spesifikasi)) {
            return 'Tidak ada spesifikasi';
        }

        $formatted = [];
        foreach ($spesifikasi as $key => $value) {
            if (!empty($value)) {
                $label = match($key) {
                    'processor' => 'Processor',
                    'ram' => 'RAM',
                    'storage' => 'Storage',
                    'vga' => 'VGA/GPU',
                    'motherboard' => 'Motherboard',
                    'psu' => 'Power Supply',
                    'brand' => 'Merek',
                    'model' => 'Model',
                    'garansi' => 'Garansi',
                    default => ucfirst($key)
                };
                $formatted[] = "{$label}: {$value}";
            }
        }

        return implode(' | ', $formatted);
    }

    /**
     * Validasi data barang sebelum disimpan
     */
    public static function validateBarangData(array $data): array
    {
        $errors = [];

        // Validasi serial number
        if (empty($data['serial_number'])) {
            $errors[] = 'Serial number harus diisi';
        } elseif (self::isSerialNumberExists($data['serial_number'])) {
            $errors[] = 'Serial number sudah ada dalam sistem';
        }

        // Validasi kode barang
        if (empty($data['kode_barang'])) {
            $errors[] = 'Kode barang harus diisi';
        } elseif (Barang::where('kode_barang', $data['kode_barang'])->exists()) {
            $errors[] = 'Kode barang sudah ada dalam sistem';
        }

        // Validasi nama barang
        if (empty($data['nama_barang'])) {
            $errors[] = 'Nama barang harus diisi';
        }

        // Validasi kategori
        if (empty($data['kategori_id'])) {
            $errors[] = 'Kategori harus dipilih';
        } elseif (!Kategori::find($data['kategori_id'])) {
            $errors[] = 'Kategori tidak valid';
        }

        // Validasi jumlah
        if (empty($data['jumlah_barang_masuk']) || $data['jumlah_barang_masuk'] < 1) {
            $errors[] = 'Jumlah barang masuk harus lebih dari 0';
        }

        return $errors;
    }

    /**
     * Membuat summary untuk notifikasi
     */
    public static function createSummaryMessage(string $namaBarang, string $serialNumber, int $jumlah, bool $isNew = true): string
    {
        if ($isNew) {
            return "Barang baru '{$namaBarang}' dengan serial number '{$serialNumber}' berhasil ditambahkan sebanyak {$jumlah} unit.";
        } else {
            $totalStock = self::getTotalStockByName($namaBarang);
            $unitCount = self::getUnitCountByName($namaBarang);
            
            return "Unit baru '{$namaBarang}' dengan serial number '{$serialNumber}' berhasil ditambahkan sebanyak {$jumlah} unit. Total stock: {$totalStock} unit dalam {$unitCount} unit terpisah.";
        }
    }
}