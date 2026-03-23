<?php

namespace App\Models;

use App\Enums\TableStatus;
use Database\Factories\FloorPlanElementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FloorPlanElement extends Model
{
    /** @use HasFactory<FloorPlanElementFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'floor_plan_id',
        'image_id',
        'x',
        'y',
        'width',
        'height',
        'rotation',
        'z_index',
        'is_table',
        'table_name',
        'seat_count',
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
            'is_table' => 'boolean',
            'seat_count' => 'integer',
            'status' => TableStatus::class,
        ];
    }

    public function floorPlan(): BelongsTo
    {
        return $this->belongsTo(FloorPlan::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
