<?php

namespace App\Models;

use App\Enums\MaintenanceTaskStatus;
use Database\Factories\MaintenanceTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceTask extends Model
{
    /** @use HasFactory<MaintenanceTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'status',
        'done_at',
        'notes',
        'assigned_to',
        'requirements',
    ];

    protected function casts(): array
    {
        return [
            'status' => MaintenanceTaskStatus::class,
            'done_at' => 'datetime',
            'requirements' => 'array',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isAssignee(User $user): bool
    {
        return $this->assigned_to === $user->id;
    }

    /**
     * Returns the display name of the assigned user, or a fallback.
     */
    public function assignedUserName(): string
    {
        if ($this->assigned_to === null) {
            return 'Unassigned';
        }

        return $this->assignedUser?->name ?? 'Nonexistent User';
    }

    public function markAsDone(): void
    {
        $this->update([
            'status' => MaintenanceTaskStatus::Done,
            'done_at' => now(),
        ]);
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', MaintenanceTaskStatus::Assigned)->orderBy('created_at', 'desc');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', MaintenanceTaskStatus::InProgress)->orderBy('created_at', 'desc');
    }

    public function scopeDone($query)
    {
        return $query->where('status', MaintenanceTaskStatus::Done)->orderBy('updated_at', 'desc');
    }
}
