<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Image extends BaseModel
{
    use SoftDeletes;

    protected $table = 'images';

    protected $fillable = [
        'ai',
        'caption',
        'author',
        'hash',
        'file',
        'file_data',
        'crop_status',
        'crop_data',
    ];

    protected function casts(): array
    {
        return [
            'ai' => 'boolean',
            'file_data' => 'array',
            'crop_status' => 'boolean',
        ];
    }

    public function postImages(): HasMany
    {
        return $this->hasMany(PostImage::class);
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'posts_images')
            ->withPivot(['caption', 'author'])
            ->withTimestamps();
    }

    public function getFilePathAttribute()
    {
        return 'images/'.$this->file;
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
