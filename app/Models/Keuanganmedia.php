<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Keuanganmedia extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'keuanganmedia';

    protected $fillable = [
        'uuid',
        'model_type',
        'model_id',
        'collection_name',
        'name',
        'file_name',
        'disk',
        'mime_type',
        'size',
        'order_column',
        'manipulations',
        'custom_properties',
        'generated_conversions',
        'responsive_images',
    ];

    protected $casts = [
        'manipulations' => 'array',
        'custom_properties' => 'array',
        'generated_conversions' => 'array',
        'responsive_images' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function folder()
    {
        return $this->belongsTo(Keuanganfolder::class, 'model_id')
            ->where('model_type', Keuanganfolder::class);
    }

    public function model()
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    public function getUrlAttribute(): string
    {
        // Langsung return URL berdasarkan disk
        if ($this->disk === 'public') {
            return asset('storage/' . $this->file_name);
        }

        // Untuk disk lainnya, gunakan URL helper
        return Storage::disk($this->disk)->exists($this->file_name)
            ? asset(Storage::url($this->file_name))
            : url('storage/' . $this->file_name);
    }

    public function getPublicUrl(): string
    {
        try {
            // Jika file ada di disk public
            if ($this->disk === 'public') {
                return asset('storage/' . $this->file_name);
            }

            // Jika file ada di disk local, copy ke public jika belum ada
            if ($this->disk === 'local') {
                if (!Storage::disk('public')->exists($this->file_name)) {
                    // Copy file dari local ke public
                    $fileContent = Storage::disk('local')->get($this->file_name);
                    Storage::disk('public')->put($this->file_name, $fileContent);
                }

                return asset('storage/' . $this->file_name);
            }

            // Fallback
            return '#';
        } catch (\Exception $e) {
            return '#';
        }
    }

    public function getPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->file_name);
    }

    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

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

    public function scopeRecentlyUploaded($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_column')->orderBy('created_at', 'desc');
    }

    public function scopeFolder($query, $folderId = null)
    {
        if (!$folderId) {
            $folderId = session()->get('folder_id');
        }

        if ($folderId) {
            $query->where('model_type', Keuanganfolder::class)
                ->where('model_id', $folderId);
        }

        return $query;
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_name);
    }

    public function deleteFile(): bool
    {
        if ($this->exists()) {
            return Storage::disk($this->disk)->delete($this->file_name);
        }

        return true;
    }

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }

            if (empty($model->user_id) && filament()->auth()->check()) {
                $model->user_id = filament()->auth()->id();
            }


            // Set default values untuk mencegah null constraint error
            if (empty($model->model_type)) {
                $model->model_type = Keuanganfolder::class;
            }

            if (empty($model->collection_name)) {
                $model->collection_name = 'default';
            }

            if (empty($model->manipulations)) {
                $model->manipulations = [];
            }

            if (empty($model->custom_properties)) {
                $model->custom_properties = [];
            }

            if (empty($model->generated_conversions)) {
                $model->generated_conversions = [];
            }

            if (empty($model->responsive_images)) {
                $model->responsive_images = [];
            }
        });

        static::deleting(function ($model) {
            $model->deleteFile();
        });

        static::addGlobalScope('userScope', function (Builder $builder) {
            if (filament()->auth()->check()) {
                $builder->where('user_id', filament()->auth()->id());
            }
        });
    }

    public function hasCustomProperty(string $key): bool
    {
        return isset($this->custom_properties[$key]) && !empty($this->custom_properties[$key]);
    }

    public function getCustomProperty(string $key, $default = null)
    {
        return $this->custom_properties[$key] ?? $default;
    }

    public function setCustomProperty(string $key, $value): self
    {
        $customProperties = $this->custom_properties ?? [];
        $customProperties[$key] = $value;
        $this->custom_properties = $customProperties;

        return $this;
    }

    public function removeCustomProperty(string $key): self
    {
        $customProperties = $this->custom_properties ?? [];
        unset($customProperties[$key]);
        $this->custom_properties = $customProperties;

        return $this;
    }

    public function getCustomProperties(): array
    {
        $properties = $this->custom_properties;

        // Pastikan return array
        if (is_string($properties)) {
            return json_decode($properties, true) ?? [];
        }

        return is_array($properties) ? $properties : [];
    }

    public function getDisplayableCustomProperties(): array
    {
        $properties = $this->getCustomProperties();

        // Exclude properties yang sudah ditampilkan di tempat lain
        $excludeKeys = ['title', 'description'];

        return array_filter($properties, function ($value, $key) use ($excludeKeys) {
            return !empty($value) && !in_array($key, $excludeKeys);
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk media dalam folder tertentu
     */
    public function scopeInFolder(Builder $query, int $folderId): Builder
    {
        return $query->where('model_type', Keuanganfolder::class)
            ->where('model_id', $folderId);
    }

    public function canBeAccessedBy(?int $userId = null): bool
    {
        $userId = $userId ?? filament()->auth()->id();

        // Jika tidak ada user yang login
        if (!$userId) {
            return false;
        }

        // Jika user adalah pemilik media
        if ($this->user_id === $userId) {
            return true;
        }

        // Jika media ada dalam folder, cek akses folder
        if ($this->model_type === Keuanganfolder::class && $this->folder) {
            return $this->folder->canBeAccessedBy($userId);
        }

        return false;
    }
}
