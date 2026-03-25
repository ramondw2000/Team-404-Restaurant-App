<?php

use App\Livewire\RoleManagement;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    (new RoleSeeder)->run();
});

/**
 * Helper: create a management user and act as them.
 */
function managementUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('management');

    return $user;
}

// ── Rendering ────────────────────────────────────────────────

it('renders the role management component', function () {
    $user = managementUser();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->assertStatus(200);
});

it('shows all seeded roles in the sidebar', function () {
    $user = managementUser();

    $component = Livewire::actingAs($user)->test(RoleManagement::class);

    foreach (['management', 'server', 'chef', 'receptionist', 'bar_staff', 'maintenance_crew'] as $roleName) {
        $component->assertSee(ucwords(str_replace(['_', '-'], ' ', $roleName)));
    }
});

it('selects the first role by default', function () {
    $user = managementUser();
    $first = Role::orderBy('name')->first();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->assertSet('selectedRoleId', $first->id);
});

// ── Role creation ─────────────────────────────────────────────

it('creates a new role', function () {
    $user = managementUser();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('openCreateForm')
        ->set('formName', 'Sommelier')
        ->set('formColor', 'indigo')
        ->call('saveRole');

    expect(Role::where('name', 'Sommelier')->exists())->toBeTrue();
});

it('validates required name when creating role', function () {
    $user = managementUser();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('openCreateForm')
        ->set('formName', '')
        ->call('saveRole')
        ->assertHasErrors(['formName']);
});

it('validates unique name when creating role', function () {
    $user = managementUser();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('openCreateForm')
        ->set('formName', 'management')
        ->set('formColor', 'blue')
        ->call('saveRole')
        ->assertHasErrors(['formName']);
});

it('validates color value when creating role', function () {
    $user = managementUser();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('openCreateForm')
        ->set('formName', 'New Role')
        ->set('formColor', 'invalid-color')
        ->call('saveRole')
        ->assertHasErrors(['formColor']);
});

// ── Role editing ──────────────────────────────────────────────

it('updates a role name and color', function () {
    $user = managementUser();
    $role = Role::where('name', 'server')->first();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('openEditForm', $role->id)
        ->set('formName', 'Head Server')
        ->set('formColor', 'cyan')
        ->call('saveRole');

    expect(Role::find($role->id)->name)->toBe('Head Server');
    expect(Role::find($role->id)->color)->toBe('cyan');
});

it('cannot rename the management role', function () {
    $user = managementUser();
    $mgmtRole = Role::where('name', 'management')->first();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('openEditForm', $mgmtRole->id)
        ->set('formName', 'Admins')
        ->call('saveRole')
        ->assertHasErrors(['formName']);
});

// ── Role deletion ─────────────────────────────────────────────

it('deletes a non-protected role and cascades from users', function () {
    $user = managementUser();
    $role = Role::where('name', 'server')->first();
    $staffer = User::factory()->create();
    $staffer->assignRole('server');

    expect($staffer->hasRole('server'))->toBeTrue();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('confirmDelete', $role->id)
        ->call('deleteRole');

    expect(Role::where('name', 'server')->exists())->toBeFalse();
    expect($staffer->fresh()->hasRole('server'))->toBeFalse();
});

it('cannot delete the management role', function () {
    $user = managementUser();
    $mgmtRole = Role::where('name', 'management')->first();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('confirmDelete', $mgmtRole->id);

    // deleteConfirmRoleId should not be set for management
    expect(Role::where('name', 'management')->exists())->toBeTrue();
});

// ── Administrator toggle ──────────────────────────────────────

it('toggles administrator on a non-management role', function () {
    $user = managementUser();
    $serverRole = Role::where('name', 'server')->first();

    expect($serverRole->is_administrator)->toBeFalse();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $serverRole->id)
        ->call('toggleAdministrator');

    expect($serverRole->fresh()->is_administrator)->toBeTrue();
});

it('cannot disable the administrator toggle on the management role', function () {
    $user = managementUser();
    $mgmtRole = Role::where('name', 'management')->first();

    expect($mgmtRole->is_administrator)->toBeTrue();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $mgmtRole->id)
        ->call('toggleAdministrator');

    expect($mgmtRole->fresh()->is_administrator)->toBeTrue();
});

// ── Permission toggling ───────────────────────────────────────

it('grants a permission to a role', function () {
    $user = managementUser();
    $serverRole = Role::where('name', 'server')->first();

    expect($serverRole->hasPermissionTo('View Dishes'))->toBeFalse();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $serverRole->id)
        ->call('togglePermission', 'View Dishes');

    expect($serverRole->fresh()->hasPermissionTo('View Dishes'))->toBeTrue();
});

it('revokes a permission from a role', function () {
    $user = managementUser();
    $serverRole = Role::where('name', 'server')->first();

    // Server has 'View Orders' by default
    expect($serverRole->hasPermissionTo('View Orders'))->toBeTrue();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $serverRole->id)
        ->call('togglePermission', 'View Orders');

    expect($serverRole->fresh()->hasPermissionTo('View Orders'))->toBeFalse();
});

it('does not toggle permissions for administrator roles', function () {
    $user = managementUser();
    $mgmtRole = Role::where('name', 'management')->first();

    $permCountBefore = $mgmtRole->permissions()->count();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $mgmtRole->id)
        ->call('togglePermission', 'View Dishes');

    expect($mgmtRole->fresh()->permissions()->count())->toBe($permCountBefore);
});

it('ignores unknown permission names', function () {
    $user = managementUser();
    $serverRole = Role::where('name', 'server')->first();

    $permCountBefore = $serverRole->permissions()->count();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $serverRole->id)
        ->call('togglePermission', 'nonexistent permission');

    expect($serverRole->fresh()->permissions()->count())->toBe($permCountBefore);
});

// ── View-gate behavior ────────────────────────────────────────

it('cannot enable a gated permission when its view gate is off', function () {
    $user = managementUser();
    $serverRole = Role::where('name', 'server')->first();

    // Server does not have View Dishes, so Add Dishes is blocked by the gate
    expect($serverRole->hasPermissionTo('View Dishes'))->toBeFalse();
    expect($serverRole->hasPermissionTo('Add Dishes'))->toBeFalse();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $serverRole->id)
        ->call('togglePermission', 'Add Dishes');

    // Should remain ungranted since view gate is off
    expect($serverRole->fresh()->hasPermissionTo('Add Dishes'))->toBeFalse();
});

it('auto-revokes gated permissions when the view gate is disabled', function () {
    $user = managementUser();
    $serverRole = Role::where('name', 'server')->first();

    // Server has View Orders and several action permissions
    expect($serverRole->hasPermissionTo('View Orders'))->toBeTrue();
    expect($serverRole->hasPermissionTo('Create Order'))->toBeTrue();
    expect($serverRole->hasPermissionTo('Cancel Order'))->toBeTrue();

    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $serverRole->id)
        ->call('togglePermission', 'View Orders');

    $serverRole->refresh();
    expect($serverRole->hasPermissionTo('View Orders'))->toBeFalse();
    expect($serverRole->hasPermissionTo('Create Order'))->toBeFalse();
    expect($serverRole->hasPermissionTo('Cancel Order'))->toBeFalse();
    expect($serverRole->hasPermissionTo('Edit Order'))->toBeFalse();
});

it('allows enabling a gated permission once its view gate is on', function () {
    $user = managementUser();
    $serverRole = Role::where('name', 'server')->first();

    // Enable view gate first
    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $serverRole->id)
        ->call('togglePermission', 'View Dishes');

    expect($serverRole->fresh()->hasPermissionTo('View Dishes'))->toBeTrue();

    // Now the gated permission can be enabled
    Livewire::actingAs($user)
        ->test(RoleManagement::class)
        ->call('selectRole', $serverRole->id)
        ->call('togglePermission', 'Add Dishes');

    expect($serverRole->fresh()->hasPermissionTo('Add Dishes'))->toBeTrue();
});
