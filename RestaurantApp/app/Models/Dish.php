<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'allergens',
        'dietary',
        'color',
        'photo_path',
    ];

    protected $casts = [
        'allergens' => 'array',
        'dietary' => 'array',
        'price' => 'decimal:2',
    ];
}
