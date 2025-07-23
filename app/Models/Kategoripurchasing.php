<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategoripurchasing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_kategori',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationship dengan folder
    public function purchasingfolder()
    {
        return $this->hasMany(Purchasingfolder::class, 'kategori_id');
    }

    // Scope untuk pencarian berdasarkan nama
    public function scopeByName(Builder $query, string $name): Builder
    {
        return $query->where('nama_kategori', 'like', '%' . $name . '%');
    }

    // Scope untuk mendapatkan kategori yang memiliki folder
    public function scopeWithFolders(Builder $query): Builder
    {
        return $query->whereHas('purchasingfolder');
    }

    // Method untuk mendapatkan jumlah folder dalam kategori
    public function getFolderCountAttribute(): int
    {
        return $this->purchasingfolder()->count();
    }

    // Method untuk mengecek apakah kategori masih digunakan
    public function isInUse(): bool
    {
        return $this->purchasingfolder()->exists();
    }

    // Override delete untuk mencegah penghapusan kategori yang masih digunakan
    public function delete()
    {
        if ($this->isInUse()) {
            throw new \Exception('Kategori tidak dapat dihapus karena masih digunakan oleh folder.');
        }

        return parent::delete();
    }

    // Method untuk pindahkan semua folder ke kategori lain sebelum hapus
    public function moveAllFoldersTo(?int $newKategoriId = null): bool
    {
        $this->purchasingfolder()->update([
            'kategori_id' => $newKategoriId
        ]);

        return true;
    }

    // Method toString untuk menampilkan nama kategori
    public function __toString(): string
    {
        return $this->nama_kategori;
    }
}
