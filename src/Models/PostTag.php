<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTag extends BaseModel
{
    public $timestamps = false;

    protected $table = 'posts_tags';

    protected $fillable = [
        'post_id',
        'tag_id',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
