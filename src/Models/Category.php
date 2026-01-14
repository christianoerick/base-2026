<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends BaseModel
{
    use SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'author_id',
        'status',
        'header',
        'footer',
        'name',
        'type',
        'key',
        'color',
        'intro',
        'details',
        'notes',
        'image',
        'social',
        'extra',
        'slug',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'header' => 'boolean',
            'footer' => 'boolean',
            'social' => 'array',
            'extra' => 'array',
        ];
    }

    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class, 'categories_domains');
    }

    public function getImagePathAttribute()
    {
        return 'categories/'.$this->image;
    }
}
