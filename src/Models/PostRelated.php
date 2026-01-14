<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostRelated extends BaseModel
{
    use SoftDeletes;

    protected $table = 'posts_related';

    protected $fillable = [
        'post_id',
        'related_id',
        'sequence',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function related(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
