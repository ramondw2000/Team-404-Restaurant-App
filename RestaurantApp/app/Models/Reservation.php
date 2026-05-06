<?php

namespace App\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;
    protected $fillable = [
        'guest_name',
        'phone',
        'email',
        'party_size',
        'reservation_datetime',
        'table_number',
        'floor_plan_element_id',
        'room_number',
        'status',
        'arrived_at',
        'internal_notes',
        'deposit_amount',
        'deposit_status',
    ];

    protected function casts(): array
    {
        return [
            'reservation_datetime' => 'datetime',
            'arrived_at' => 'datetime',
            'party_size' => 'integer',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function floorPlanElement(): BelongsTo
    {
        return $this->belongsTo(FloorPlanElement::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['scheduled', 'arrived']);
    }
}
