<?php

namespace Database\Seeders;

use App\Models\MaintenanceTask;
use Illuminate\Database\Seeder;

class MaintenanceTaskSeeder extends Seeder
{
    public function run(): void
    {
        $pending = [
            'Reparatie koelkast in keuken',
            'Vervanging lamp boven tafel 12',
            'Controle brandmelders restaurant',
            'Reparatie deurslot personeelsingang',
            'Onderhoud espressomachine',
            'Onderhoud vaatwasser',
            'Reparatie terrasverwarming',
            'Vervanging tafelkleed tafel 5',
            'Reparatie wijnkoeler',
        ];

        $completed = [
            ['name' => 'Lekkage kraan bij bar', 'notes' => 'Kraan vervangen, probleem opgelost'],
            ['name' => 'Schoonmaak afzuigkap keuken', 'notes' => 'Afzuigkap gereinigd en filters vervangen'],
            ['name' => 'Reparatie stoel tafel 8', 'notes' => 'Stoel poot vastgezet'],
            ['name' => 'Controle nooduitgang restaurant', 'notes' => 'Nooduitgang gecontroleerd, werkt correct'],
            ['name' => 'Schoonmaak vloer keuken', 'notes' => 'Vloer grondig gereinigd en ontsmet'],
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
