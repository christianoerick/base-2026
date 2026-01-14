<?php

namespace ChristianoErick\Base\Models;

class FormSubmission extends BaseModel
{
    protected $table = 'form_submissions';

    protected $fillable = [
        'status',
        'api_status',
        'form_type',
        'name',
        'email',
        'phone',
        'message',
        'ip_address',
        'user_agent',
        'extra_data',
        'api_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'api_status' => 'boolean',
            'extra_data' => 'array',
        ];
    }
}
