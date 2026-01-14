<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PostIntegration extends BaseModel
{
    use SoftDeletes;

    protected $table = 'posts_integrations';

    protected $fillable = [
        'post_id',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
