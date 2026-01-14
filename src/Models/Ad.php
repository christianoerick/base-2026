<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends BaseModel
{
    use SoftDeletes;

    protected $table = 'ads';

    protected $fillable = [
        'status',
        'name',
        'type',
        'size',
        'content',
        'url',
        'date_fixed',
        'date_from',
        'date_to',
        'domain_all',
        'place_all',
        'place_all_categories',
        'views',
        'clicks',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'date_fixed' => 'boolean',
            'domain_all' => 'boolean',
            'place_all' => 'boolean',
            'place_all_categories' => 'boolean',
        ];
    }
}
