<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends BaseModel
{
    use SoftDeletes;

    protected $table = 'authors';

    protected $fillable = [
        'status',
        'name',
        'intro',
        'details',
        'image',
        'social',
        'slug',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'social' => 'array',
        ];
    }

    public function getImagePathAttribute()
    {
        return 'authors/'.$this->image;
    }
}
