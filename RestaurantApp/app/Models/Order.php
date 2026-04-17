<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'floor_plan_element_id',
        'reservation_id',
        'user_id',
        'status',
        'paid',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'paid' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<FloorPlanElement, $this>
     */
    public function floorPlanElement(): BelongsTo
    {
        return $this->belongsTo(FloorPlanElement::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return BelongsTo<Reservation, $this>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
