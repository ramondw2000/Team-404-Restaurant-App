<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'guest_name',
        'phone',
        'email',
        'party_size',
        'reservation_datetime',
        'table_number',
        'room_number',
        'status',
        'internal_notes',
        'deposit_amount',
        'deposit_status',
    ];

    protected $casts = [
        'reservation_datetime' => 'datetime',
        'party_size' => 'integer',
        'deposit_amount' => 'decimal:2',
    ];
}
