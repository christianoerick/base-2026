<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdPlace extends BaseModel
{
    use SoftDeletes;

    protected $table = 'ads_places';

    protected $fillable = [
        'ad_id',
        'place',
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }
}
