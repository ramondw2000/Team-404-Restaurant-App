<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Role definitions: name, color, is_administrator, and granted permissions.
     *
     * @var array<string, array{color: string, is_administrator: bool, permissions: list<string>}>
     */
    private const array ROLES = [
        'management' => [
            'color' => 'purple',
            'is_administrator' => true,
            'permissions' => [], // Administrator bypasses all checks
        ],
        'server' => [
            'color' => 'blue',
            'is_administrator' => false,
            'permissions' => [
                // Order management: servers create and manage orders
                'View Orders', 'Create Order', 'Edit Order', 'Cancel Order', 'Process Payment', 'Assign Table',
                // Can view kitchen and bar orders to check status
                'View Kitchen Orders', 'View Bar Orders',
                // Table management for seating guests
                'View Table Management',
            ],
        ],
        'chef' => [
            'color' => 'orange',
            'is_administrator' => false,
            'permissions' => [
                // Dish management
                'View Dishes', 'Add Dishes', 'Edit Dishes', 'Delete Dishes',
                // Kitchen order fulfillment
                'View Kitchen Orders', 'Mark Orders Ready',
            ],
        ],
        'bartender' => [
            'color' => 'amber',
            'is_administrator' => false,
            'permissions' => [
                // Bar order fulfillment
                'View Bar Orders', 'Send Bar Orders',
            ],
        ],
        'receptionist' => [
            'color' => 'green',
            'is_administrator' => false,
            'permissions' => [
                // Reservations management
                'View Reservations', 'Create Reservation', 'Edit Reservation', 'Cancel Reservation',
                // Table management for seating
                'View Table Management', 'Update Table Status',
            ],
        ],
        'barista' => [
            'color' => 'cyan',
            'is_administrator' => false,
            'permissions' => [
                // Hybrid role: helps with both kitchen and bar
                'View Kitchen Orders', 'View Bar Orders',
                'Mark Orders Ready', 'Send Bar Orders',
            ],
        ],
        'maintenance_crew' => [
            'color' => 'rose',
            'is_administrator' => false,
            'permissions' => [
                // Can view table management to see which tables need attention
                'View Table Management',
                'View Maintenance', 'Create Maintenance Task', 'Edit Maintenance Task', 'Complete Maintenance Task',
            ],
        ],
    ];

    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRoles();
    }

    private function seedPermissions(): void
    {
        foreach (PermissionRegistry::allNames() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    private function seedRoles(): void
    {
        foreach (self::ROLES as $name => $config) {
            $role = Role::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['color' => $config['color'], 'is_administrator' => $config['is_administrator']],
            );

            // Update color/is_administrator on existing roles too
            $role->update([
                'color' => $config['color'],
                'is_administrator' => $config['is_administrator'],
            ]);

            if (! $config['is_administrator']) {
                $role->syncPermissions($config['permissions']);
            }
        }
    }
}
