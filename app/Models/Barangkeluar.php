<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barangkeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'barangkeluars';

    protected $fillable = [
        'barang_id',
        'pengajuan_id',
        'user_id',
        'jumlah_barang_keluar',
        'tanggal_keluar_barang',
        'keterangan',
        'project_name',
        'status',
        'kategori_id',
        'sumber'
    ];

    protected $casts = [
        'tanggal_keluar_barang' => 'date',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function subkategori()
    {
        return $this->belongsTo(Subkategori::class);
    }

    // Accessor untuk mendapatkan total jumlah barang keluar dari semua detail
    public function getTotalJumlahKeluarAttribute()
    {
        return $this->details->sum('jumlah_barang_keluar');
    }

    // Accessor untuk mendapatkan jumlah item yang berbeda
    public function getTotalItemAttribute()
    {
        return $this->details->count();
    }

    // Method untuk mendapatkan ringkasan barang keluar
    public function getRingkasanBarangAttribute()
    {
        return $this->details->map(function ($detail) {
            return [
                'barang' => $detail->barang->nama_barang,
                'serial_number' => $detail->barang->serial_number,
                'jumlah' => $detail->jumlah_barang_keluar,
                'subkategori' => $detail->barang->subkategori->nama_subkategori ?? '-',
            ];
        });
    }

    // Scope untuk filter berdasarkan stok rendah
    public function scopeWithStokRendah($query, $batas = 5)
    {
        return $query->whereHas('barang', function ($q) use ($batas) {
            $q->where('jumlah_barang', '<=', $batas);
        });
    }

    // Scope untuk filter berdasarkan periode
    public function scopePeriode($query, $dari, $sampai)
    {
        return $query->whereBetween('tanggal_keluar_barang', [$dari, $sampai]);
    }

    // Scope untuk filter berdasarkan subkategori
    public function scopeBySubkategori($query, $subkategoriId)
    {
        return $query->whereHas('barang', function ($q) use ($subkategoriId) {
            $q->where('subkategori_id', $subkategoriId);
        });
    }
}
