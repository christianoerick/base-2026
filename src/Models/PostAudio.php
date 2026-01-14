<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostAudio extends BaseModel
{
    use SoftDeletes;

    protected $table = 'posts_audios';

    protected $fillable = [
        'post_id',
        'audio_id',
        'caption',
        'sequence',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function audio(): BelongsTo
    {
        return $this->belongsTo(Audio::class);
    }
}
