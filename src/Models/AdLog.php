<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdLog extends BaseModel
{
    public $timestamps = false;

    protected $table = 'ads_logs';

    protected $fillable = [
        'ad_id',
        'domain_id',
        'views',
        'clicks',
        'created_at',
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
