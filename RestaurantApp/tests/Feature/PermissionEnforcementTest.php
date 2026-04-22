<?php

use App\Models\Dish;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    (new RoleSeeder)->run();
});

/**
 * Helper: create a user with the given role.
 */
function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ── Administrator bypass ──────────────────────────────────────

it('allows an administrator role to access all permission-protected routes', function () {
    $user = userWithRole('management');

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $this->actingAs($user)->get(route('accounts.index'))->assertOk();
    $this->actingAs($user)->get(route('dishes'))->assertOk();
    $this->actingAs($user)->get(route('kitchen-orders'))->assertOk();
    $this->actingAs($user)->get(route('statistics'))->assertOk();
    $this->actingAs($user)->get(route('tablemanagement'))->assertOk();
    $this->actingAs($user)->get(route('maintenance'))->assertOk();
});

// ── Dashboard: accessible to all authenticated users ──────────

it('allows any authenticated user to access the dashboard without a permission', function () {
    $user = userWithRole('server'); // server has no dashboard-specific permission

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

// ── Permission-based route access ─────────────────────────────

it('enforces View Dishes permission on the dishes route', function () {
    $user = userWithRole('server'); // no View Dishes permission

    $this->actingAs($user)->get(route('dishes'))->assertForbidden();
});

it('allows a role with View Dishes to access the dishes route', function () {
    $user = userWithRole('chef'); // has View Dishes

    $this->actingAs($user)->get(route('dishes'))->assertOk();
});

it('enforces View Kitchen Orders permission on the kitchen orders route', function () {
    $user = userWithRole('bartender'); // no View Kitchen Orders permission

    $this->actingAs($user)->get(route('kitchen-orders'))->assertForbidden();
});

it('enforces View Account Management permission on the accounts route', function () {
    $user = userWithRole('server'); // no View Account Management permission

    $this->actingAs($user)->get(route('accounts.index'))->assertForbidden();
});

it('enforces View Statistics permission on the statistics route', function () {
    $user = userWithRole('server'); // no View Statistics permission

    $this->actingAs($user)->get(route('statistics'))->assertForbidden();
});

it('enforces View Table Management permission on the table management route', function () {
    $user = userWithRole('chef'); // no View Table Management permission

    $this->actingAs($user)->get(route('tablemanagement'))->assertForbidden();
});

it('allows a role with View Table Management to access table management', function () {
    $user = userWithRole('maintenance_crew'); // has View Table Management

    $this->actingAs($user)->get(route('tablemanagement'))->assertOk();
});

// ── Dynamic permission enforcement ───────────────────────────

it('grants access immediately when a permission is added to a role', function () {
    $user = userWithRole('server'); // no View Dishes by default

    $this->actingAs($user)->get(route('dishes'))->assertForbidden();

    // Add the permission to the server role
    $serverRole = Role::where('name', 'server')->first();
    $serverRole->givePermissionTo('View Dishes');

    // Clear Spatie's permission cache so changes take effect
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->actingAs($user)->get(route('dishes'))->assertOk();
});

it('revokes access immediately when a permission is removed from a role', function () {
    $user = userWithRole('chef'); // has View Dishes by default

    $this->actingAs($user)->get(route('dishes'))->assertOk();

    // Remove the permission
    $chefRole = Role::where('name', 'chef')->first();
    $chefRole->revokePermissionTo('View Dishes');

    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->actingAs($user)->get(route('dishes'))->assertForbidden();
});

// ── Per-action account route enforcement (#3) ───────────────

it('denies a user with only View Account Management from creating an account', function () {
    $user = userWithRole('server');
    $viewOnlyRole = Role::where('name', 'server')->first();
    $viewOnlyRole->givePermissionTo('View Account Management');
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->actingAs($user)
        ->post(route('accounts.store'), [
            'name' => 'New Staff',
            'email' => 'newstaff@example.com',
            'password' => 'password123',
            'roles' => ['server'],
        ])
        ->assertForbidden();
});

it('denies a user with only View Account Management from deleting an account', function () {
    $target = userWithRole('server');
    $user = userWithRole('server');
    $viewOnlyRole = Role::where('name', 'server')->first();
    $viewOnlyRole->givePermissionTo('View Account Management');
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->actingAs($user)
        ->delete(route('accounts.destroy', $target))
        ->assertForbidden();
});

// ── Dish permission enforcement (#1) ─────────────────────────

it('enforces Add Dishes permission on dish creation route', function () {
    $user = userWithRole('server'); // no Add Dishes

    $this->actingAs($user)
        ->post(route('dishes.store'), ['name' => 'Test Dish', 'price' => '10.00'])
        ->assertForbidden();
});

it('enforces Edit Dishes permission on dish update route', function () {
    $dish = Dish::factory()->create();
    $user = userWithRole('server'); // no Edit Dishes

    $this->actingAs($user)
        ->post(route('dishes.update', $dish), ['name' => 'Updated', 'price' => '12.00'])
        ->assertForbidden();
});

it('enforces Delete Dishes permission on dish destroy route', function () {
    $dish = Dish::factory()->create();
    $user = userWithRole('server'); // no Delete Dishes

    $this->actingAs($user)
        ->delete(route('dishes.destroy', $dish))
        ->assertForbidden();
});

// ── Self-lockout prevention ───────────────────────────────────

it('prevents a manager from removing the management role from themselves', function () {
    $manager = userWithRole('management');

    $this->actingAs($manager)
        ->put(route('accounts.update', $manager), [
            'name' => $manager->name,
            'email' => $manager->email,
            'roles' => ['server'], // Remove management from self
        ])
        ->assertRedirect(route('accounts.index'));

    // Should still have management role
    expect($manager->fresh()->hasRole('management'))->toBeTrue();
});

it('prevents a manager from deleting their own account', function () {
    $manager = userWithRole('management');

    $this->actingAs($manager)
        ->delete(route('accounts.destroy', $manager))
        ->assertRedirect(route('accounts.index'));

    expect(User::find($manager->id))->not->toBeNull();
});

it('allows a manager to delete another user account', function () {
    $manager = userWithRole('management');
    $staffUser = userWithRole('server');

    $this->actingAs($manager)
        ->delete(route('accounts.destroy', $staffUser))
        ->assertRedirect(route('accounts.index'));

    expect(User::find($staffUser->id))->toBeNull();
});

// ── Account management page tabs ─────────────────────────────

it('renders the users tab by default', function () {
    $user = userWithRole('management');

    $this->actingAs($user)
        ->get(route('accounts.index'))
        ->assertOk()
        ->assertSee('Users')
        ->assertSee('Roles & Permissions');
});

it('renders the roles tab when requested', function () {
    $user = userWithRole('management');

    $this->actingAs($user)
        ->get(route('accounts.index', ['tab' => 'roles']))
        ->assertOk()
        ->assertSee('Roles');
});
