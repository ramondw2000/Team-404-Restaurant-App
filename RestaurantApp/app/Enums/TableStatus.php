<?php

namespace App\Enums;

enum TableStatus: string
{
    case Available = 'Available';
    case Reserved = 'Reserved';
    case Occupied = 'Occupied';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::Available => 'green',
            self::Reserved => 'amber',
            self::Occupied => 'red',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-green-100 text-green-800 ring-1 ring-inset ring-green-600/20',
            self::Reserved => 'bg-amber-100 text-amber-800 ring-1 ring-inset ring-amber-600/20',
            self::Occupied => 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20',
        };
    }

    public function dotClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-green-500',
            self::Reserved => 'bg-amber-500',
            self::Occupied => 'bg-red-500',
        };
    }
}
