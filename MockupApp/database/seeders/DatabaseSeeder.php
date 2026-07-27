<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $staff = [
            ['name' => 'Test User',          'email' => 'test@example.com',                      'role' => 'management'],
            ['name' => 'Sofia Ricci',        'email' => 'sofia.ricci@molvenoresort.it',           'role' => 'server'],
            ['name' => 'Marco De Luca',      'email' => 'marco.deluca@molvenoresort.it',          'role' => 'server'],
            ['name' => 'Elena Verdi',        'email' => 'elena.verdi@molvenoresort.it',           'role' => 'server'],
            ['name' => 'Giovanni Esposito',  'email' => 'giovanni.esposito@molvenoresort.it',     'role' => 'chef'],
            ['name' => 'Chiara Fontana',     'email' => 'chiara.fontana@molvenoresort.it',        'role' => 'chef'],
            ['name' => 'Roberto Conti',      'email' => 'roberto.conti@molvenoresort.it',         'role' => 'chef'],
            ['name' => 'Alessia Mancini',    'email' => 'alessia.mancini@molvenoresort.it',       'role' => 'receptionist'],
            ['name' => 'Davide Ferretti',    'email' => 'davide.ferretti@molvenoresort.it',       'role' => 'receptionist'],
            ['name' => 'Valentina Greco',    'email' => 'valentina.greco@molvenoresort.it',       'role' => 'management'],
        ];

        foreach ($staff as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'role'     => $data['role'],
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                ]
            );
        }
    }
}
