<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends BaseModel
{
    use SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'domain_id',
        'author_id',
        'category_id',
        'image_id',
        'audio_id',
        'file_id',
        'status',
        'ai',
        'category_highlight',
        'title',
        'subtitle',
        'hat',
        'text_type',
        'text',
        'text_items',
        'author',
        'embed',
        'embed_extra',
        'publish_date',
        'update_status',
        'update_date',
        'key',
        'type',
        'image_caption',
        'image_author',
        'image_crop',
        'audio_caption',
        'file_caption',
        'canonical_url',
        'views',
        'seo',
        'integration_id',
        'extra_data',
        'slug',
        'sequence',
        'internal_created_by',
        'internal_updated_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $appends = [
        'image_path',
        'author_name',
        'created_by_name',
        'updated_by_name',
        'deleted_by_name',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'ai' => 'boolean',
            'category_highlight' => 'boolean',
            'update_status' => 'boolean',
            'text_items' => 'array',
            'image_crop' => 'array',
            'seo' => 'array',
            'extra_data' => 'array',
        ];
    }

    public function author_item(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'image_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function audio(): BelongsTo
    {
        return $this->belongsTo(Audio::class, 'audio_id');
    }

    public function domains_categories(): HasMany
    {
        return $this->hasMany(PostDomainCategory::class, 'post_id');
    }

    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class, 'posts_domains_categories', 'post_id', 'domain_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'posts_domains_categories', 'post_id', 'category_id');
    }

    public function postImages(): HasMany
    {
        return $this->hasMany(PostImage::class)
            ->orderBy('posts_images.sequence');
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'posts_images')
            ->withPivot(['caption', 'author', 'crop_data'])
            ->withTimestamps();
    }

    public function postAudios(): HasMany
    {
        return $this->hasMany(PostAudio::class)
            ->orderBy('posts_audios.sequence');
    }

    public function audios(): BelongsToMany
    {
        return $this->belongsToMany(Audio::class, 'posts_audios')
            ->withPivot(['caption'])
            ->withTimestamps();
    }

    public function postFiles(): HasMany
    {
        return $this->hasMany(PostFile::class)
            ->orderBy('posts_files.sequence');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'posts_files')
            ->withPivot(['caption'])
            ->withTimestamps();
    }

    public function postRelateds(): HasMany
    {
        return $this->hasMany(PostRelated::class)
            ->orderBy('posts_related.sequence');
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'posts_related', 'post_id', 'related_id')
            ->orderBy('sequence');
    }

    public function postTags(): HasMany
    {
        return $this->hasMany(PostTag::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'posts_tags');
    }

    public function getImagePathAttribute()
    {
        if (is_object($this->image)) {
            $image = 'images/' . $this->image->file;
        } elseif (str_contains($this->embed_extra, 'videos/')) {
            $image = $this->embed_extra;
        } else {
            $image = 'no-photo.png';
        }

        return $image;
    }

    public function getImageSize($size)
    {
        return cdnImage($this->image_path, $size);
    }

    public function getGalleryDataAttribute()
    {
        if (is_object($this->image)) {
            $image = cdnImage('images/'.$this->image->file, '300x200', $this->image->crop_data);
        } else {
            $image = cdnImage('no-photo.png', '300x200');
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $image,
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', true)
            ->where(function ($q) {
                $q->whereNull('publish_date')
                    ->orWhere('publish_date', '<=', date('Y-m-d H:i:').'00');
            });
    }

    public function rotaSimples($prefix = '', $suffix = '')
    {
        $params = [
            $this->type,
        ];

        if (!empty($prefix))
        {
            $params[] = trim($prefix, '/');
        }

        if ($d = $this->publish_date)
        {
            $params[] = date('Y/m/d', strtotime($d));
        }

        $params[] = $this->id.'-'.substr($item->slug, 0, 249 - strlen($item->id));

        if (!empty($suffix))
        {
            $params[] = trim($suffix, '/');
        }

        return '/'.implode('/', $params);
    }

    public function rotaCategoria($prefix = '', $suffix = '')
    {
        $params = [
            $this->type,
        ];

        if (!empty($prefix))
        {
            $params[] = trim($prefix, '/');
        }

        if ($this->category_id && is_object($this->category) && isset($this->category->slug))
        {
            $params[] = $this->category->slug;
        }

        if ($d = $this->publish_date)
        {
            $params[] = date('Y/m/d', strtotime($d));
        }

        $params[] = $this->id.'-'.substr($item->slug, 0, 249 - strlen($item->id));

        if (!empty($suffix))
        {
            $params[] = trim($suffix, '/');
        }

        return '/'.implode('/', $params);
    }
}
