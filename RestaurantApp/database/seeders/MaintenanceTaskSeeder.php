<?php

namespace Database\Seeders;

use App\Models\MaintenanceTask;
use Illuminate\Database\Seeder;

class MaintenanceTaskSeeder extends Seeder
{
    public function run(): void
    {
        $pending = [
            ['name' => 'Repair kitchen fridge', 'location' => 'Kitchen'],
            ['name' => 'Replace lamp above table 12', 'location' => 'Dining room'],
            ['name' => 'Inspect restaurant fire alarms', 'location' => 'Entrance'],
            ['name' => 'Repair staff entrance door lock', 'location' => 'Staff room'],
            ['name' => 'Service espresso machine', 'location' => 'Bar'],
            ['name' => 'Service dishwasher', 'location' => 'Kitchen'],
            ['name' => 'Repair terrace heater', 'location' => 'Terrace'],
            ['name' => 'Replace tablecloth table 5', 'location' => 'Dining room'],
            ['name' => 'Repair wine cooler', 'location' => 'Bar'],
        ];

        $completed = [
            ['name' => 'Fix leaking tap at bar', 'location' => 'Bar', 'notes' => 'Tap replaced, problem resolved'],
            ['name' => 'Clean kitchen exhaust hood', 'location' => 'Kitchen', 'notes' => 'Hood cleaned and filters replaced'],
            ['name' => 'Repair chair at table 8', 'location' => 'Dining room', 'notes' => 'Chair leg tightened'],
            ['name' => 'Inspect restaurant emergency exit', 'location' => 'Entrance', 'notes' => 'Emergency exit checked, working correctly'],
            ['name' => 'Clean kitchen floor', 'location' => 'Kitchen', 'notes' => 'Floor thoroughly cleaned and disinfected'],
        ];

        foreach ($pending as $task) {
            MaintenanceTask::firstOrCreate(
                ['name' => $task['name']],
                ['location' => $task['location'], 'status' => 'pending', 'notes' => null],
            );
        }

        foreach ($completed as $task) {
            MaintenanceTask::firstOrCreate(
                ['name' => $task['name']],
                ['location' => $task['location'], 'status' => 'completed', 'notes' => $task['notes']],
            );
        }
    }
}
