<?php

namespace App\Support;

class PermissionRegistry
{
    /**
     * Permission groups, each with a label, optional view gate, and list of permissions.
     *
     * The view_gate is the permission name that must be enabled before any other
     * permission in the group can be granted. Groups with view_gate = null have no gate.
     *
     * @var array<int, array{key: string, label: string, view_gate: string|null, permissions: list<array{name: string, description: string}>}>
     */
    public const array GROUPS = [
        [
            'key' => 'dishes',
            'label' => 'Dishes',
            'view_gate' => 'View Dishes',
            'permissions' => [
                ['name' => 'View Dishes', 'description' => 'Access the Dishes page'],
                ['name' => 'Add Dishes', 'description' => 'Create new dishes'],
                ['name' => 'Edit Dishes', 'description' => 'Modify existing dishes'],
                ['name' => 'Delete Dishes', 'description' => 'Remove dishes permanently'],
            ],
        ],
        [
            'key' => 'kitchen_orders',
            'label' => 'Kitchen Orders',
            'view_gate' => 'View Kitchen Orders',
            'permissions' => [
                ['name' => 'View Kitchen Orders', 'description' => 'Access the Kitchen Orders page'],
                ['name' => 'Mark Orders Ready', 'description' => 'Mark a kitchen order or individual item as ready'],
            ],
        ],
        [
            'key' => 'bar_orders',
            'label' => 'Bar Orders',
            'view_gate' => 'View Bar Orders',
            'permissions' => [
                ['name' => 'View Bar Orders', 'description' => 'Access the Bar Orders page'],
                ['name' => 'Send Bar Orders', 'description' => 'Mark a bar ticket as ready and send it out'],
                ['name' => 'Create Bar Order', 'description' => 'Start a standalone bar order (no table required)'],
            ],
        ],
        [
            'key' => 'orders',
            'label' => 'Orders',
            'view_gate' => 'View Orders',
            'permissions' => [
                ['name' => 'View Orders', 'description' => 'Access the Order Management page'],
                ['name' => 'Create Order', 'description' => 'Start a new customer order'],
                ['name' => 'Edit Order', 'description' => 'Modify items on an existing order'],
                ['name' => 'Cancel Order', 'description' => 'Cancel an active order'],
                ['name' => 'Process Payment', 'description' => 'Mark an order as paid / process checkout'],
                ['name' => 'Assign Table', 'description' => 'Assign or reassign a table to an order'],
            ],
        ],
        [
            'key' => 'account_management',
            'label' => 'Account Management',
            'view_gate' => 'View Account Management',
            'permissions' => [
                ['name' => 'View Account Management', 'description' => 'Access the Account Management page'],
                ['name' => 'Create User', 'description' => 'Add a new staff account'],
                ['name' => 'Edit User', 'description' => 'Update an existing staff account'],
                ['name' => 'Delete User', 'description' => 'Remove a staff account'],
                ['name' => 'Manage Roles', 'description' => 'Access the Roles & Permissions tab; create, edit, delete roles and assign permissions'],
            ],
        ],
        [
            'key' => 'table_management',
            'label' => 'Table Management',
            'view_gate' => 'View Table Management',
            'permissions' => [
                ['name' => 'View Table Management', 'description' => 'Access the Table Management page'],
                ['name' => 'Edit Table Layout', 'description' => 'Enter edit mode; place, move, resize, and delete floor plan elements'],
                ['name' => 'Manage Floor Plans', 'description' => 'Create and delete floor plans; upload background images'],
                ['name' => 'Update Table Status', 'description' => "Change a table's status (Available / Occupied / Reserved) outside of edit mode"],
            ],
        ],
        [
            'key' => 'reservations',
            'label' => 'Reservations',
            'view_gate' => 'View Reservations',
            'permissions' => [
                ['name' => 'View Reservations', 'description' => 'Access the Reservations page'],
                ['name' => 'Create Reservation', 'description' => 'Create a new reservation'],
                ['name' => 'Edit Reservation', 'description' => 'Modify an existing reservation'],
                ['name' => 'Cancel Reservation', 'description' => 'Cancel a reservation'],
            ],
        ],
        [
            'key' => 'statistics',
            'label' => 'Statistics',
            'view_gate' => 'View Statistics',
            'permissions' => [
                ['name' => 'View Statistics', 'description' => 'Access the Statistics page (read-only)'],
            ],
        ],
        [
            'key' => 'maintenance',
            'label' => 'Maintenance',
            'view_gate' => 'View Maintenance',
            'permissions' => [
                ['name' => 'View Maintenance', 'description' => 'Access the Maintenance Tasks page'],
                ['name' => 'Create Maintenance Task', 'description' => 'Add a new maintenance task'],
                ['name' => 'Edit Maintenance Task', 'description' => 'Edit notes on a maintenance task'],
                ['name' => 'Complete Maintenance Task', 'description' => 'Mark a maintenance task as done'],
                ['name' => 'Assign Maintenance Task', 'description' => 'Assign other users to maintenance tasks'],
                ['name' => 'Delete Maintenance Task', 'description' => 'Delete a maintenance task'],
            ],
        ],
    ];

    /**
     * Returns all permission names as a flat list.
     *
     * @return list<string>
     */
    public static function allNames(): array
    {
        return array_values(array_merge(...array_map(
            fn (array $group) => array_column($group['permissions'], 'name'),
            self::GROUPS,
        )));
    }

    /**
     * Returns whether the given permission name is the view gate for a group.
     */
    public static function isViewGate(string $permissionName): bool
    {
        foreach (self::GROUPS as $group) {
            if ($group['view_gate'] === $permissionName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the view gate permission name that guards the given permission,
     * or null if the permission is itself a view gate or has no gate.
     */
    public static function viewGateFor(string $permissionName): ?string
    {
        foreach (self::GROUPS as $group) {
            $names = array_column($group['permissions'], 'name');

            if (in_array($permissionName, $names, true) && $group['view_gate'] !== $permissionName) {
                return $group['view_gate'];
            }
        }

        return null;
    }

    /**
     * Returns all action permission names gated by the given view gate.
     * The view gate itself is not included in the result.
     *
     * @return list<string>
     */
    public static function permissionsGatedBy(string $viewGateName): array
    {
        foreach (self::GROUPS as $group) {
            if ($group['view_gate'] === $viewGateName) {
                return array_values(array_filter(
                    array_column($group['permissions'], 'name'),
                    fn (string $name) => $name !== $viewGateName,
                ));
            }
        }

        return [];
    }
}
