<?php

namespace Database\Seeders;

use App\Models\MaintenanceTask;
use Illuminate\Database\Seeder;

class MaintenanceTaskSeeder extends Seeder
{
    public function run(): void
    {
        $pending = [
            'Repair kitchen fridge',
            'Replace lamp above table 12',
            'Inspect restaurant fire alarms',
            'Repair staff entrance door lock',
            'Service espresso machine',
            'Service dishwasher',
            'Repair terrace heater',
            'Replace tablecloth table 5',
            'Repair wine cooler',
        ];

        $completed = [
            ['name' => 'Fix leaking tap at bar', 'notes' => 'Tap replaced, problem resolved'],
            ['name' => 'Clean kitchen exhaust hood', 'notes' => 'Hood cleaned and filters replaced'],
            ['name' => 'Repair chair at table 8', 'notes' => 'Chair leg tightened'],
            ['name' => 'Inspect restaurant emergency exit', 'notes' => 'Emergency exit checked, working correctly'],
            ['name' => 'Clean kitchen floor', 'notes' => 'Floor thoroughly cleaned and disinfected'],
        ];

        foreach ($pending as $name) {
            MaintenanceTask::firstOrCreate(
                ['name' => $name],
                ['status' => 'pending', 'notes' => null],
            );
        }

        foreach ($completed as $task) {
            MaintenanceTask::firstOrCreate(
                ['name' => $task['name']],
                ['status' => 'completed', 'notes' => $task['notes']],
            );
        }
    }
}
