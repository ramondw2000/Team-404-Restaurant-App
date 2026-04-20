<?php

namespace Database\Seeders;

use App\Enums\TableStatus;
use App\Models\FloorPlan;
use App\Models\FloorPlanElement;
use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FloorPlanSeeder extends Seeder
{
    public function run(): void
    {
        $image = $this->seedFloorPlanImage();
        $floorPlan = $this->seedFloorPlan($image);
        $this->seedTables($floorPlan);
    }

    private function seedFloorPlanImage(): Image
    {
        Storage::disk('public')->makeDirectory('images');

        $sourcePath = base_path('../docs/images/floorplan.webp');
        $storageName = 'images/floorplan.webp';

        if (File::exists($sourcePath)) {
            Storage::disk('public')->put($storageName, File::get($sourcePath));
        }

        [$width, $height] = $this->resolveWebpDimensions($sourcePath);

        return Image::create([
            'filename'          => 'floorplan.webp',
            'original_filename' => 'floorplan.webp',
            'path'              => $storageName,
            'mime_type'         => 'image/webp',
            'size'              => File::exists($sourcePath) ? File::size($sourcePath) : 0,
            'width'             => $width,
            'height'            => $height,
        ]);
    }

    private function seedFloorPlan(Image $image): FloorPlan
    {
        return FloorPlan::create([
            'name'                => 'Main Floor',
            'background_image_id' => $image->id,
        ]);
    }

    /**
     * Seed a set of realistic demo tables for the floor plan.
     *
     * @param  FloorPlan  $floorPlan
     */
    private function seedTables(FloorPlan $floorPlan): void
    {
        /** @var array<int, array{shape: string, seats: int, x: float, y: float, w: float, h: float, name: string, status: TableStatus}> $tables */
        $tables = [
            ['shape' => 'rectangular',  'seats' => 4,  'x' => 10.0, 'y' => 30.0, 'w' => 6.1, 'h' => 4.2,  'name' => 'Table 1',  'status' => TableStatus::Available],
            ['shape' => 'rectangular',  'seats' => 4,  'x' => 28.0, 'y' => 30.0, 'w' => 6.1, 'h' => 4.2,  'name' => 'Table 2',  'status' => TableStatus::Available],
            ['shape' => 'rectangular',  'seats' => 6,  'x' => 70.0, 'y' => 30.0, 'w' => 7.4, 'h' => 5.1,  'name' => 'Table 3',  'status' => TableStatus::Available],
            ['shape' => 'round',        'seats' => 6,  'x' => 10.0, 'y' => 55.0, 'w' => 6.8, 'h' => 6.8,  'name' => 'Table 4',  'status' => TableStatus::Available],
            ['shape' => 'round',        'seats' => 6,  'x' => 28.0, 'y' => 55.0, 'w' => 6.8, 'h' => 6.8,  'name' => 'Table 5',  'status' => TableStatus::Reserved],
            ['shape' => 'rectangular',  'seats' => 8,  'x' => 48.0, 'y' => 52.0, 'w' => 11.2, 'h' => 6.0, 'name' => 'Table 6', 'status' => TableStatus::Available],
            ['shape' => 'rectangular',  'seats' => 8,  'x' => 65.0, 'y' => 52.0, 'w' => 11.2, 'h' => 6.0, 'name' => 'Table 7', 'status' => TableStatus::Available],
        ];

        foreach ($tables as $i => $table) {
            FloorPlanElement::create([
                'floor_plan_id' => $floorPlan->id,
                'shape'         => $table['shape'],
                'seat_count'    => $table['seats'],
                'x'             => $table['x'],
                'y'             => $table['y'],
                'width'         => $table['w'],
                'height'        => $table['h'],
                'rotation'      => 0.0,
                'z_index'       => $i,
                'table_name'    => $table['name'],
                'status'        => $table['status'],
            ]);
        }
    }

    /**
     * Attempt to read width/height from a WebP file header.
     *
     * @return array{int|null, int|null}
     */
    private function resolveWebpDimensions(string $path): array
    {
        if (! File::exists($path)) {
            return [null, null];
        }

        $info = @getimagesize($path);

        return $info ? [$info[0], $info[1]] : [null, null];
    }
}
