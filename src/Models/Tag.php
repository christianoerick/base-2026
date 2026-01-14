<?php

namespace ChristianoErick\Base\Models;

class Tag extends BaseModel
{
    protected $table = 'tags';

    protected $fillable = [
        'tag',
        'slug',
    ];
}
