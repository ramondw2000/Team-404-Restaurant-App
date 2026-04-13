<?php

namespace App\Models;

use App\Enums\MaintenanceTaskStatus;
use Database\Factories\MaintenanceTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTask extends Model
{
    /** @use HasFactory<MaintenanceTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MaintenanceTaskStatus::class,
        ];
    }
}
