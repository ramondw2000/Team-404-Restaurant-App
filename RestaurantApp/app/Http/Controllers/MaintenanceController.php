<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    /**
     * Display the maintenance tasks overview.
     */
    public function index(): View
    {
        // Mock data for design purposes - no functionality
        $tasks = [
            [
                'id' => 1,
                'name' => 'Reparatie koelkast in keuken',
                'date' => '2026-04-10 09:30',
                'status' => 'pending',
                'notes' => ''
            ],
            [
                'id' => 2,
                'name' => 'Vervanging lamp boven tafel 12',
                'date' => '2026-04-10 10:15',
                'status' => 'pending',
                'notes' => ''
            ],
            [
                'id' => 3,
                'name' => 'Lekkage kraan bij bar',
                'date' => '2026-04-09 14:20',
                'status' => 'completed',
                'notes' => 'Kraan vervangen, probleem opgelost'
            ],
            [
                'id' => 4,
                'name' => 'Schoonmaak afzuigkap keuken',
                'date' => '2026-04-09 08:00',
                'status' => 'completed',
                'notes' => 'Afzuigkap gereinigd en filters vervangen'
            ],
            [
                'id' => 5,
                'name' => 'Controle brandmelders restaurant',
                'date' => '2026-04-10 11:00',
                'status' => 'pending',
                'notes' => ''
            ],
            [
                'id' => 6,
                'name' => 'Reparatie deurslot personeelsingang',
                'date' => '2026-04-10 13:45',
                'status' => 'pending',
                'notes' => ''
            ],
            [
                'id' => 7,
                'name' => 'Onderhoud espressomachine',
                'date' => '2026-04-10 14:30',
                'status' => 'pending',
                'notes' => ''
            ],
            [
                'id' => 8,
                'name' => 'Reparatie stoel tafel 8',
                'date' => '2026-04-09 16:00',
                'status' => 'completed',
                'notes' => 'Stoel poot vastgezet'
            ],
            [
                'id' => 9,
                'name' => 'Onderhoud vaatwasser',
                'date' => '2026-04-10 15:00',
                'status' => 'pending',
                'notes' => ''
            ],
            [
                'id' => 10,
                'name' => 'Reparatie terrasverwarming',
                'date' => '2026-04-10 16:30',
                'status' => 'pending',
                'notes' => ''
            ],
            [
                'id' => 11,
                'name' => 'Controle nooduitgang restaurant',
                'date' => '2026-04-09 11:00',
                'status' => 'completed',
                'notes' => 'Nooduitgang gecontroleerd, werkt correct'
            ],
            [
                'id' => 12,
                'name' => 'Vervanging tafelkleed tafel 5',
                'date' => '2026-04-10 08:00',
                'status' => 'pending',
                'notes' => ''
            ],
            [
                'id' => 13,
                'name' => 'Reparatie wijnkoeler',
                'date' => '2026-04-10 17:00',
                'status' => 'pending',
                'notes' => ''
            ],
            [
                'id' => 14,
                'name' => 'Schoonmaak vloer keuken',
                'date' => '2026-04-09 18:30',
                'status' => 'completed',
                'notes' => 'Vloer grondig gereinigd en ontsmet'
            ],
        ];

        return view('maintenance.index', [
            'tasks' => $tasks
        ]);
    }
}
