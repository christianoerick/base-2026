<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PostOriginal extends BaseModel
{
    use SoftDeletes;

    protected $table = 'posts_originals';

    protected $fillable = [
        'post_id',
        'category_id',
        'image_id',
        'title',
        'subtitle',
        'hat',
        'author',
        'image_caption',
        'image_author',
        'slug',
        'text',
        'publish_date',
        'canonical_url',
        'integration_id',
    ];
}
