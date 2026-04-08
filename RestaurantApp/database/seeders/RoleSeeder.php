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
                'View Orders', 'Create Order', 'Edit Order', 'Cancel Order', 'Process Payment', 'Assign Table',
                'View Table Management',
            ],
        ],
        'chef' => [
            'color' => 'orange',
            'is_administrator' => false,
            'permissions' => [
                'View Dishes',
                'View Kitchen Orders', 'Mark Orders Ready',
            ],
        ],
        'receptionist' => [
            'color' => 'green',
            'is_administrator' => false,
            'permissions' => [
                'View Orders', 'Create Order', 'Edit Order', 'Assign Table',
                'View Kitchen Orders',
                'View Table Management', 'Update Table Status',
                'View Reservations', 'Create Reservation', 'Edit Reservation', 'Cancel Reservation',
            ],
        ],
        'bar_staff' => [
            'color' => 'amber',
            'is_administrator' => false,
            'permissions' => [
                'View Dishes',
                'View Kitchen Orders', 'Mark Orders Ready',
            ],
        ],
        'maintenance_crew' => [
            'color' => 'rose',
            'is_administrator' => false,
            'permissions' => [
                'View Table Management', 'Edit Table Layout', 'Manage Floor Plans',
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
