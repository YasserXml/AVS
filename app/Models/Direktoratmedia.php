<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;

class Direktoratmedia extends Model
{   
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'model_type',
        'model_id',
        'name',
        'file_name',
        'disk',
        'mime_type',
        'size',
        'order_column',
    ];



     public function model()
    {
        return $this->morphTo();
    }

    /**
     * Accessor untuk mendapatkan URL file
     */
    public function getUrlAttribute(): string
    {
        if ($this->disk === 'public') {
            return asset('storage/' . $this->file_name);
        }
        return Storage::disk($this->disk)->path($this->file_name);
    }

    /**
     * Accessor untuk mendapatkan path lengkap file
     */
    public function getPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->file_name);
    }

    /**
     * Accessor untuk mendapatkan ukuran file yang dapat dibaca manusia
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Accessor untuk mendapatkan tipe media
     */
    public function getMediaTypeAttribute(): string
    {
        if (str_starts_with($this->mime_type, 'image/')) {
            return 'image';
        } elseif (str_starts_with($this->mime_type, 'video/')) {
            return 'video';
        } elseif (str_starts_with($this->mime_type, 'audio/')) {
            return 'audio';
        } elseif ($this->mime_type === 'application/pdf') {
            return 'pdf';
        } elseif (in_array($this->mime_type, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            return 'document';
        } elseif (in_array($this->mime_type, [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ])) {
            return 'spreadsheet';
        } else {
            return 'file';
        }
    }

    /**
     * Accessor untuk mendapatkan icon berdasarkan tipe file
     */
    public function getIconAttribute(): string
    {
        return match ($this->media_type) {
            'image' => 'heroicon-o-photo',
            'video' => 'heroicon-o-video-camera',
            'audio' => 'heroicon-o-musical-note',
            'pdf' => 'heroicon-o-document-text',
            'document' => 'heroicon-o-document',
            'spreadsheet' => 'heroicon-o-table-cells',
            default => 'heroicon-o-document'
        };
    }

    /**
     * Scope untuk filter berdasarkan tipe media
     */
    public function scopeByMediaType($query, string $type)
    {
        return match ($type) {
            'images' => $query->where('mime_type', 'like', 'image/%'),
            'videos' => $query->where('mime_type', 'like', 'video/%'),
            'audio' => $query->where('mime_type', 'like', 'audio/%'),
            'documents' => $query->whereIn('mime_type', [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]),
            default => $query
        };
    }

    /**
     * Scope untuk media yang diunggah dalam rentang waktu tertentu
     */
    public function scopeRecentlyUploaded($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope untuk urutkan berdasarkan order_column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_column')->orderBy('created_at', 'desc');
    }

    /**
     * Method untuk cek apakah file ada di storage
     */
    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_name);
    }

    /**
     * Method untuk hapus file dari storage
     */
    public function deleteFile(): bool
    {
        if ($this->exists()) {
            return Storage::disk($this->disk)->delete($this->file_name);
        }
        
        return true;
    }

    /**
     * Method untuk duplikasi media
     */
    public function duplicate(): self
    {
        $newMedia = $this->replicate();
        $newMedia->uuid = Str::uuid();
        $newMedia->name = $this->name . ' (Copy)';
        
        // Copy file jika diperlukan
        $pathinfo = pathinfo($this->file_name);
        $newFileName = $pathinfo['filename'] . '_copy.' . $pathinfo['extension'];
        
        if (Storage::disk($this->disk)->copy($this->file_name, $newFileName)) {
            $newMedia->file_name = $newFileName;
        }
        
        $newMedia->save();
        
        return $newMedia;
    }

    /**
     * Boot method untuk auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
        
        static::deleting(function ($model) {
            $model->deleteFile();
        });
    }
}
