<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'is_administrator' => 'boolean',
        ];
    }
}
