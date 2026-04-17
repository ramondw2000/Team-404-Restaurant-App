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
            ['shape' => 'rectangular',  'seats' => 4,  'x' => 10.0, 'y' => 30.0, 'w' => 13.0, 'h' => 8.0,  'name' => 'Table 5',  'status' => TableStatus::Available],
            ['shape' => 'rectangular',  'seats' => 4,  'x' => 28.0, 'y' => 30.0, 'w' => 13.0, 'h' => 8.0,  'name' => 'Table 6',  'status' => TableStatus::Available],
            ['shape' => 'rectangular',  'seats' => 6,  'x' => 48.0, 'y' => 30.0, 'w' => 16.0, 'h' => 8.0,  'name' => 'Table 7',  'status' => TableStatus::Occupied],
            ['shape' => 'rectangular',  'seats' => 6,  'x' => 70.0, 'y' => 30.0, 'w' => 16.0, 'h' => 8.0,  'name' => 'Table 8',  'status' => TableStatus::Available],
            ['shape' => 'round',        'seats' => 6,  'x' => 10.0, 'y' => 55.0, 'w' => 12.0, 'h' => 12.0, 'name' => 'Table 9',  'status' => TableStatus::Available],
            ['shape' => 'round',        'seats' => 6,  'x' => 28.0, 'y' => 55.0, 'w' => 12.0, 'h' => 12.0, 'name' => 'Table 10', 'status' => TableStatus::Reserved],
            ['shape' => 'rectangular',  'seats' => 8,  'x' => 48.0, 'y' => 52.0, 'w' => 18.0, 'h' => 10.0, 'name' => 'Table 11', 'status' => TableStatus::Available],
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
