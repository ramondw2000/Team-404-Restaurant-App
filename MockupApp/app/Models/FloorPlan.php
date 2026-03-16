<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FloorPlan extends Model
{
    /** @use HasFactory<\Database\Factories\FloorPlanFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'background_image_id',
    ];

    protected function casts(): array
    {
        return [
            'background_image_id' => 'integer',
        ];
    }

    public function backgroundImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'background_image_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(FloorPlanElement::class)->orderBy('z_index');
    }

    public function activeElements(): HasMany
    {
        return $this->hasMany(FloorPlanElement::class)->orderBy('z_index');
    }
}
