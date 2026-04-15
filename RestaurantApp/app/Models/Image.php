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
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
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
        return $this->floorPlans()->exists();
    }
}
