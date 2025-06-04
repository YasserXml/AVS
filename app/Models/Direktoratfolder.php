<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Direktoratfolder extends Model 
{
    use InteractsWithMedia;

    protected $fillable = [
        'parent_id',
        'model_type',
        'model_id',
        'name',
        'collection',
        'description',
        'icon',
        'color',
        'is_protected',
        'password',
        'is_hidden',
        'is_favorite',
        'is_public',
        'has_user_access',
        'user_id',
        'user_type',
    ];

    protected $casts =[
        'is_protected' => 'boolean',
        'is_hidden' => 'boolean',
        'is_favorite' => 'boolean',
        'is_public' => 'boolean',
        'has_user_access' => 'boolean'
    ];

    public function model()
    {
        return $this->morphTo();
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke media yang ada di folder ini
     */
    public function media()
    {
        return $this->hasMany(DirektoratMedia::class, 'model_id')
            ->where('model_type', self::class);
    }

    /**
     * Relasi ke subfolder
     */
    public function subfolders()
    {
        return $this->hasMany(self::class, 'model_id')
            ->where('model_type', self::class);
    }

    /**
     * Relasi ke parent folder
     */
    public function parentFolder()
    {
        return $this->belongsTo(self::class, 'model_id');
    }

    /**
     * Scope untuk folder publik
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope untuk folder favorit
     */
    public function scopeFavorite($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope untuk folder yang tidak tersembunyi
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    /**
     * Scope untuk folder berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accessor untuk mendapatkan jumlah total item (media + subfolder)
     */
    public function getTotalItemsAttribute()
    {
        return $this->media()->count() + $this->subfolders()->count();
    }

    /**
     * Accessor untuk mendapatkan path lengkap folder
     */
    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parentFolder;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parentFolder;
        }
        
        return implode(' / ', $path);
    }

    /**
     * Method untuk cek apakah folder memiliki password
     */
    public function isProtected(): bool
    {
        return $this->is_protected && !empty($this->password);
    }

    /**
     * Method untuk cek password folder
     */
    public function checkPassword(string $password): bool
    {
        return $this->isProtected() && hash_equals($this->password, $password);
    }

    /**
     * Method untuk mendapatkan icon default berdasarkan konten
     */
    public function getDefaultIcon(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        $mediaCount = $this->media()->count();
        $folderCount = $this->subfolders()->count();

        if ($mediaCount > 0 && $folderCount === 0) {
            return 'heroicon-o-document-duplicate';
        } elseif ($folderCount > 0) {
            return 'heroicon-o-folder-open';
        }

        return 'heroicon-o-folder';
    }

    /**
     * Method untuk mendapatkan warna default
     */
    public function getDefaultColor(): string
    {
        return $this->color ?? '#10b981';
    }

}
