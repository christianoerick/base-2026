<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Audio extends BaseModel
{
    use SoftDeletes;

    protected $table = 'audios';

    protected $fillable = [
        'ai',
        'caption',
        'hash',
        'duration',
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

    public function getFilePathAttribute()
    {
        return 'audios/'.$this->file;
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
