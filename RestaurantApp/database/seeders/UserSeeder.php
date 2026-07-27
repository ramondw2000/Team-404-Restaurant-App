<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Demo accounts — one per default role.
     * All accounts use the password: password
     *
     * @var array<string, array{name: string, email: string, role: string}>
     */
    private const array DEMO_ACCOUNTS = [
        [
            'name'  => 'Demo Manager',
            'email' => 'manager@demo.com',
            'role'  => 'management',
        ],
        [
            'name'  => 'Demo Server',
            'email' => 'server@demo.com',
            'role'  => 'server',
        ],
        [
            'name'  => 'Demo Chef',
            'email' => 'chef@demo.com',
            'role'  => 'chef',
        ],
        [
            'name'  => 'Demo Bartender',
            'email' => 'bartender@demo.com',
            'role'  => 'bartender',
        ],
        [
            'name'  => 'Demo Receptionist',
            'email' => 'receptionist@demo.com',
            'role'  => 'receptionist',
        ],
        [
            'name'  => 'Demo Barista',
            'email' => 'barista@demo.com',
            'role'  => 'barista',
        ],
        [
            'name'  => 'Demo Maintenance',
            'email' => 'maintenance@demo.com',
            'role'  => 'maintenance_crew',
        ],
    ];

    public function run(): void
    {
        foreach (self::DEMO_ACCOUNTS as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name'              => $account['name'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$account['role']]);
        }
    }
}
