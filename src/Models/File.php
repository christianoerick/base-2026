<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends BaseModel
{
    use SoftDeletes;

    protected $table = 'files';

    protected $fillable = [
        'ai',
        'caption',
        'author',
        'hash',
        'file',
        'file_data',
    ];

    protected function casts(): array
    {
        return [
            'ai' => 'boolean',
            'file_data' => 'array',
        ];
    }

    public function getImageAttribute()
    {
        return secure_url('/assets/ext/'.pathinfo($this->file, PATHINFO_EXTENSION).'.png');
    }

    public function getFilePathAttribute()
    {
        return 'files/'.$this->file;
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
