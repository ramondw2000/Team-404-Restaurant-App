<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'draft',
    ];

    public const DEFAULT_CATEGORIES = ['Starters', 'Mains', 'Desserts', 'Drinks', 'Sides'];

    /**
     * @return HasMany<MenuCategory, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(MenuCategory::class)->orderBy('sort_order');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Seed default categories on a newly created menu.
     */
    public function seedDefaultCategories(): void
    {
        foreach (self::DEFAULT_CATEGORIES as $index => $name) {
            $this->categories()->create([
                'name' => $name,
                'sort_order' => $index,
            ]);
        }
    }
}
