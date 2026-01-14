<?php

namespace ChristianoErick\Base\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    private static array $list_social = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'x' => 'X (Twitter)',
    ];

    public static function getListSocial(): array
    {
        return self::$list_social;
    }

    public function getFillable()
    {
        return $this->fillable ?? [];
    }
}
