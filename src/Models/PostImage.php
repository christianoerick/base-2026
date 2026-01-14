<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostImage extends BaseModel
{
    use SoftDeletes;

    protected $table = 'posts_images';

    protected $fillable = [
        'post_id',
        'image_id',
        'caption',
        'author',
        'crop_data',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'crop_data' => 'array',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
