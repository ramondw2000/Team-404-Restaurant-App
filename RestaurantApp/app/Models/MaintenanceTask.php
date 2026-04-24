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
        'location',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MaintenanceTaskStatus::class,
        ];
    }

    public function markAsDone(): void
    {
        $this->update(['status' => MaintenanceTaskStatus::Completed]);
    }

    public function scopePending($query)
    {
        return $query->where('status', MaintenanceTaskStatus::Pending)->orderBy('created_at', 'desc');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', MaintenanceTaskStatus::Completed)->orderBy('updated_at', 'desc');
    }
}
