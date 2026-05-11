<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    // Seed all roles and their permissions so permission-based routes work correctly
    (new RoleSeeder)->run();
});

/**
 * Helper: create a user with the given role and act as them.
 */
function actingAsRole(string $role): mixed
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return test()->actingAs($user);
}

// ── Dashboard: all authenticated users ──────────────────────────

it('allows management to access the dashboard', function () {
    actingAsRole('management')->get(route('dashboard'))->assertOk();
});

it('allows receptionist to access the dashboard', function () {
    actingAsRole('receptionist')->get(route('dashboard'))->assertOk();
});

it('allows server to access the dashboard', function () {
    actingAsRole('server')->get(route('dashboard'))->assertOk();
});

it('allows chef to access the dashboard', function () {
    actingAsRole('chef')->get(route('dashboard'))->assertOk();
});

it('allows bartender to access the dashboard', function () {
    actingAsRole('bartender')->get(route('dashboard'))->assertOk();
});

it('allows barista to access the dashboard', function () {
    actingAsRole('barista')->get(route('dashboard'))->assertOk();
});

it('allows maintenance_crew to access the dashboard', function () {
    actingAsRole('maintenance_crew')->get(route('dashboard'))->assertOk();
});

// ── Statistics: management only (Administrator bypass) ──

it('allows management to access statistics', function () {
    actingAsRole('management')->get(route('statistics'))->assertOk();
});

it('denies server access to statistics', function () {
    actingAsRole('server')->get(route('statistics'))->assertForbidden();
});

it('denies receptionist access to statistics', function () {
    actingAsRole('receptionist')->get(route('statistics'))->assertForbidden();
});

it('denies chef access to statistics', function () {
    actingAsRole('chef')->get(route('statistics'))->assertForbidden();
});

// ── Account Management: management only (Administrator bypass) ──

it('allows management to access account management', function () {
    actingAsRole('management')->get(route('accounts.index'))->assertOk();
});

it('denies server access to account management', function () {
    actingAsRole('server')->get(route('accounts.index'))->assertForbidden();
});

it('denies receptionist access to account management', function () {
    actingAsRole('receptionist')->get(route('accounts.index'))->assertForbidden();
});

// ── Table Management: management, server, receptionist, maintenance_crew ──

it('allows management to access table management', function () {
    actingAsRole('management')->get(route('tablemanagement'))->assertOk();
});

it('allows server to access table management', function () {
    actingAsRole('server')->get(route('tablemanagement'))->assertOk();
});

it('allows receptionist to access table management', function () {
    actingAsRole('receptionist')->get(route('tablemanagement'))->assertOk();
});

it('allows maintenance_crew to access table management', function () {
    actingAsRole('maintenance_crew')->get(route('tablemanagement'))->assertOk();
});

it('denies chef access to table management', function () {
    actingAsRole('chef')->get(route('tablemanagement'))->assertForbidden();
});

it('denies bartender access to table management', function () {
    actingAsRole('bartender')->get(route('tablemanagement'))->assertForbidden();
});

it('denies barista access to table management', function () {
    actingAsRole('barista')->get(route('tablemanagement'))->assertForbidden();
});

// ── Dishes: management, chef ──

it('allows management to access dishes', function () {
    actingAsRole('management')->get(route('dishes'))->assertOk();
});

it('allows chef to access dishes', function () {
    actingAsRole('chef')->get(route('dishes'))->assertOk();
});

it('denies bartender access to dishes', function () {
    actingAsRole('bartender')->get(route('dishes'))->assertForbidden();
});

it('denies server access to dishes', function () {
    actingAsRole('server')->get(route('dishes'))->assertForbidden();
});

it('denies receptionist access to dishes', function () {
    actingAsRole('receptionist')->get(route('dishes'))->assertForbidden();
});

it('denies maintenance_crew access to dishes', function () {
    actingAsRole('maintenance_crew')->get(route('dishes'))->assertForbidden();
});

// ── Maintenance: management, maintenance_crew ──

it('allows management to access maintenance', function () {
    actingAsRole('management')->get(route('maintenance'))->assertOk();
});

it('allows maintenance_crew to access maintenance', function () {
    actingAsRole('maintenance_crew')->get(route('maintenance'))->assertOk();
});

it('denies server access to maintenance', function () {
    actingAsRole('server')->get(route('maintenance'))->assertForbidden();
});

it('denies chef access to maintenance', function () {
    actingAsRole('chef')->get(route('maintenance'))->assertForbidden();
});

it('denies receptionist access to maintenance', function () {
    actingAsRole('receptionist')->get(route('maintenance'))->assertForbidden();
});
