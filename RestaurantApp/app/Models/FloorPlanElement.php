<?php

namespace App\Models;

use App\Enums\TableStatus;
use Database\Factories\FloorPlanElementFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

class FloorPlanElement extends Model
{
    /** @use HasFactory<FloorPlanElementFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'floor_plan_id',
        'shape',
        'seat_count',
        'x',
        'y',
        'width',
        'height',
        'rotation',
        'z_index',
        'table_name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'x' => 'float',
            'y' => 'float',
            'width' => 'float',
            'height' => 'float',
            'rotation' => 'float',
            'z_index' => 'integer',
            'seat_count' => 'integer',
            'status' => TableStatus::class,
        ];
    }

    public function floorPlan(): BelongsTo
    {
        return $this->belongsTo(FloorPlan::class);
    }

    /**
     * Resolve the public image path for this element's shape + seat_count combination.
     *
     * Scans public/elements/{shape}/ for a file named {seat_count}.{ext} in the
     * supported image formats and returns its URL path, or null if no match exists.
     */
    protected function imagePath(): Attribute
    {
        return Attribute::get(function (): ?string {
            $extensions = ['svg', 'png', 'jpg', 'jpeg', 'webp'];
            $directory = public_path("elements/{$this->shape}");

            foreach ($extensions as $ext) {
                $file = "{$directory}/{$this->seat_count}.{$ext}";
                if (File::exists($file)) {
                    return "/elements/{$this->shape}/{$this->seat_count}.{$ext}";
                }
            }

            return null;
        });
    }
}
