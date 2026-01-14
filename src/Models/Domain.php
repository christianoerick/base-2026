<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends BaseModel
{
    use SoftDeletes;

    protected $table = 'domains';

    protected $fillable = [
        'status',
        'name',
        'color',
        'domain',
        'extra_data',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'extra_data' => 'array',
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_domains');
    }
}
