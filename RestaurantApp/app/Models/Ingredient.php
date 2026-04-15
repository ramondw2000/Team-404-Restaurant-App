<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'allergens',
        'dietary',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allergens' => 'array',
            'dietary' => 'array',
        ];
    }

    /**
     * @return BelongsToMany<Dish, $this>
     */
    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class);
    }
}
