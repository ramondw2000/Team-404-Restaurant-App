<?php

namespace App\Models;

use Database\Factories\ImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    /** @use HasFactory<ImageFactory> */
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_filename',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
        'crop_x',
        'crop_y',
        'crop_w',
        'crop_h',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'crop_x' => 'float',
            'crop_y' => 'float',
            'crop_w' => 'float',
            'crop_h' => 'float',
        ];
    }

    public function floorPlanElements(): HasMany
    {
        return $this->hasMany(FloorPlanElement::class);
    }

    public function floorPlans(): HasMany
    {
        return $this->hasMany(FloorPlan::class, 'background_image_id');
    }

    public function url(): string
    {
        return Storage::url($this->path);
    }

    public function isInUse(): bool
    {
        return $this->floorPlanElements()->exists()
            || $this->floorPlans()->exists();
    }
}
