<?php

namespace App\Enums;

enum MaintenanceTaskStatus: string
{
    case Unassigned = 'unassigned';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Unassigned => 'Unassigned',
            self::Assigned => 'Assigned',
            self::InProgress => 'In Progress',
            self::Done => 'Done',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Unassigned => 'secondary',
            self::Assigned => 'warning',
            self::InProgress => 'primary',
            self::Done => 'success',
        };
    }
}
