<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostFile extends BaseModel
{
    use SoftDeletes;

    protected $table = 'posts_files';

    protected $fillable = [
        'post_id',
        'file_id',
        'caption',
        'sequence',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
