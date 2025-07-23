<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Sekretariatfolder extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'parent_id',
        'model_type',
        'model_id',
        'slug',
        'name',
        'collection',
        'description',
        'icon',
        'color',
        'is_protected',
        'password',
        'is_hidden',
        'is_public',
        'has_user_access',
        'user_id',
        'user_type',
    ];

    protected $casts = [
        'is_protected' => 'boolean',
        'is_hidden' => 'boolean',
        'is_favorite' => 'boolean',
        'is_public' => 'boolean',
        'has_user_access' => 'boolean',
    ];


    public function model()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sekretariatmedia()
    {
        return $this->hasMany(Sekretariatmedia::class, 'model_id')
            ->where('model_type', self::class);
    }

    public function sekremedia()
    {
        return $this->sekretariatmedia();
    }

    public function kategori()
    {
        return $this->belongsTo(Kategorisekretariat::class, 'kategori_id');
    }

    // Scope untuk filter berdasarkan kategori
    public function scopeByKategori(Builder $query, $kategoriId): Builder
    {
        return $query->where('kategori_id', $kategoriId);
    }

    public function scopeWithKategori(Builder $query): Builder
    {
        return $query->with('kategori');
    }

    // Method untuk mendapatkan nama kategori
    public function getKategoriNameAttribute(): ?string
    {
        return $this->kategori?->nama_kategori;
    }

    // Method untuk cek apakah folder memiliki kategori
    public function hasKategori(): bool
    {
        return !is_null($this->kategori_id) && !is_null($this->kategori);
    }

    public function parent()
    {
        return $this->belongsTo(Sekretariatfolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Sekretariatfolder::class, 'parent_id');
    }

    public function subfolders()
    {
        return $this->children();
    }

    public function parentFolder()
    {
        return $this->parent();
    }

    public function getAllMedia()
    {
        // Gunakan direktoratmedia(), bukan media
        $media = collect($this->sekretariatmedia);

        foreach ($this->subfolders as $subfolder) {
            $media = $media->merge($subfolder->getAllMedia());
        }

        return $media;
    }

    public function deleteRecursively()
    {
        // Hapus semua media dalam folder ini
        foreach ($this->sekretariatmedia as $mediaItem) {
            if (method_exists($mediaItem, 'deleteFile')) {
                $mediaItem->deleteFile();
            }
            $mediaItem->delete();
        }

        // Hapus subfolder secara rekursif
        foreach ($this->subfolders as $subfolder) {
            $subfolder->deleteRecursively();
        }

        // Hapus folder ini
        $this->delete();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        // Cek apakah slug sudah ada (kecuali untuk record ini sendiri)
        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate slug jika belum ada
            if (empty($model->slug) && !empty($model->name)) {
                $model->slug = $model->generateUniqueSlug($model->name);
            }

            if (empty($model->user_id) && filament()->auth()->check()) {
                $model->user_id = filament()->auth()->id();
            }

            // Set default values untuk mencegah null constraint error
            if (empty($model->icon)) {
                $model->icon = 'heroicon-o-folder';
            }

            if (empty($model->color)) {
                $model->color = '#ffab09';
            }

            // Set default boolean values
            if (is_null($model->is_protected)) {
                $model->is_protected = false;
            }

            if (is_null($model->is_hidden)) {
                $model->is_hidden = false;
            }

            if (is_null($model->is_public)) {
                $model->is_public = false;
            }

            if (is_null($model->has_user_access)) {
                $model->has_user_access = false;
            }

            // Logika untuk membedakan folder root dan subfolder
            if (!is_null($model->parent_id)) {
                // Ini adalah subfolder, kosongkan collection dan model_type/model_id
                $model->collection = null;
                $model->model_type = null;
                $model->model_id = null;
            } else {
                // Ini adalah folder root, pastikan ada collection jika belum diset
                if (empty($model->collection) && !empty($model->name)) {
                    $model->collection = Str::slug($model->name);
                }
            }
        });

        static::updating(function ($model) {
            // Update slug jika name berubah dan slug kosong atau sama dengan slug lama dari name lama
            if ($model->isDirty('name')) {
                $newSlug = Str::slug($model->name);
                $oldSlug = Str::slug($model->getOriginal('name'));

                // Update slug jika kosong atau slug lama sama dengan nama lama
                if (empty($model->slug) || $model->slug === $oldSlug) {
                    $model->slug = $model->generateUniqueSlug($model->name);
                }
            }
        });

        static::addGlobalScope('userScope', function (Builder $builder) {
            if (filament()->auth()->check()) {
                $builder->where(function ($query) {
                    $query->where('user_id', filament()->auth()->id())
                        ->orWhere('is_public', true);
                });
            }
        });
    }

    // Method untuk mendapatkan URL media dengan slug
    public function getMediaUrl(): string
    {
        return route('filament.admin.resources.arsip.sekretariat.folder.index', [
            'folder' => $this->slug
        ]);
    }

    // Method untuk mendapatkan URL lengkap dengan nested path
    public function getFullUrl(): string
    {
        return route('filament.admin.resources.arsip.sekretariat.folder.index', [
            'folder' => $this->full_slug_path
        ]);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk folder public
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope untuk folder root (tanpa parent)
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }


    public function scopeMainFolders($query)
    {
        return $query->whereNull('parent_id')
            ->where(function ($q) {
                $q->whereNull('model_type')
                    ->orWhere('model_id', null);
            });
    }

    public function scopeSubfoldersOf($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    public function scopeByLevel($query, $level = 0)
    {
        if ($level === 0) {
            return $query->whereNull('parent_id');
        }

        // Untuk level yang lebih dalam, perlu recursive query
        return $query->whereNotNull('parent_id');
    }

    public function scopeStandalone($query)
    {
        return $query->whereNull('model_type')
            ->whereNull('model_id');
    }

    public function getTotalItemsAttribute()
    {
        return $this->direktoratmedia()->count() + $this->subfolders()->count();
    }

    public function getFullSlugPathAttribute(): string
    {
        $path = collect();
        $current = $this;

        while ($current) {
            $path->prepend($current->slug);
            $current = $current->parent;
        }

        return $path->join('/');
    }

    public function getFullNamePathAttribute(): string
    {
        $path = $this->name;
        $parent = $this->parent;

        while ($parent) {
            $path = $parent->name . ' / ' . $path;
            $parent = $parent->parent;
        }

        return $path;
    }


    public function isProtected(): bool
    {
        return $this->is_protected && !empty($this->password);
    }

    public function checkPassword(string $password): bool
    {
        return $this->isProtected() && hash_equals($this->password, $password);
    }

    public function getDefaultIcon(): string
    {
        if ($this->icon && $this->icon !== 'heroicon-o-folder') {
            return $this->icon;
        }

        $mediaCount = $this->sekretariatmedia()->count();
        $folderCount = $this->subfolders()->count();

        if ($mediaCount > 0 && $folderCount === 0) {
            return 'heroicon-o-document-duplicate';
        } elseif ($folderCount > 0) {
            return 'heroicon-o-folder-open';
        }

        return 'heroicon-o-folder';
    }

    public function getDefaultColor(): string
    {
        return $this->color ?? '#ffab09';
    }

    public function isRootFolder(): bool
    {
        return is_null($this->parent_id);
    }

    public function isSubfolder(): bool
    {
        return !is_null($this->parent_id);
    }

    public function getDepthLevel(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    public function canBeAccessedBy(?int $userId = null): bool
    {
        $userId = $userId ?? filament()->auth()->id();

        // Jika tidak ada user yang login
        if (!$userId) {
            return $this->is_public;
        }

        // Jika user adalah pemilik folder
        if ($this->user_id === $userId) {
            return true;
        }

        // Jika folder adalah public
        if ($this->is_public) {
            return true;
        }

        return false;
    }
}
