<?php

namespace App\Enums;

enum MaintenanceTaskStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Done',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'text-amber-700 bg-amber-50',
            self::Completed => 'text-green-700 bg-green-50',
        };
    }
}
