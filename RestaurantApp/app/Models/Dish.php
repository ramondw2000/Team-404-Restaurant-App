<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'color',
        'photo_path',
        'is_available',
        'is_bar_item',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_bar_item' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Ingredient, $this>
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return BelongsToMany<MenuCategory, $this>
     */
    public function menuCategories(): BelongsToMany
    {
        return $this->belongsToMany(MenuCategory::class, 'menu_category_dish')
            ->withPivot('sort_order');
    }

    /**
     * Allergens derived from ingredients (union).
     *
     * @return Attribute<list<string>, never>
     */
    protected function allergens(): Attribute
    {
        return Attribute::get(function (): array {
            return $this->ingredients
                ->pluck('allergens')
                ->flatten()
                ->unique()
                ->values()
                ->all();
        });
    }

    /**
     * Dietary flags derived from ingredients (intersection).
     * Dish is "vegan" only if ALL ingredients are vegan.
     * Dish is "vegetarian" only if ALL ingredients are vegetarian (or vegan).
     *
     * @return Attribute<list<string>, never>
     */
    protected function dietary(): Attribute
    {
        return Attribute::get(function (): array {
            $ingredients = $this->ingredients;

            if ($ingredients->isEmpty()) {
                return [];
            }

            $dietary = [];

            $allVegetarian = $ingredients->every(
                fn (Ingredient $i): bool => in_array('vegetarian', $i->dietary) || in_array('vegan', $i->dietary)
            );

            $allVegan = $ingredients->every(
                fn (Ingredient $i): bool => in_array('vegan', $i->dietary)
            );

            if ($allVegetarian) {
                $dietary[] = 'vegetarian';
            }

            if ($allVegan) {
                $dietary[] = 'vegan';
            }

            return $dietary;
        });
    }

    /**
     * Clean up photo from storage on deletion.
     */
    protected static function booted(): void
    {
        static::deleting(function (Dish $dish): void {
            if ($dish->photo_path) {
                Storage::disk('public')->delete($dish->photo_path);
            }
        });
    }
}
