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

it('allows bar_staff to access the dashboard', function () {
    actingAsRole('bar_staff')->get(route('dashboard'))->assertOk();
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

it('denies bar_staff access to table management', function () {
    actingAsRole('bar_staff')->get(route('tablemanagement'))->assertForbidden();
});

// ── Order Management: management, server, receptionist ──

it('allows management to access order management', function () {
    actingAsRole('management')->get(route('ordermanagement'))->assertOk();
});

it('allows server to access order management', function () {
    actingAsRole('server')->get(route('ordermanagement'))->assertOk();
});

it('allows receptionist to access order management', function () {
    actingAsRole('receptionist')->get(route('ordermanagement'))->assertOk();
});

it('denies chef access to order management', function () {
    actingAsRole('chef')->get(route('ordermanagement'))->assertForbidden();
});

it('denies bar_staff access to order management', function () {
    actingAsRole('bar_staff')->get(route('ordermanagement'))->assertForbidden();
});

it('denies maintenance_crew access to order management', function () {
    actingAsRole('maintenance_crew')->get(route('ordermanagement'))->assertForbidden();
});

// ── Kitchen Orders: management, receptionist, chef, bar_staff ──

it('allows management to access kitchen orders', function () {
    actingAsRole('management')->get(route('kitchen-orders'))->assertOk();
});

it('allows receptionist to access kitchen orders', function () {
    actingAsRole('receptionist')->get(route('kitchen-orders'))->assertOk();
});

it('allows chef to access kitchen orders', function () {
    actingAsRole('chef')->get(route('kitchen-orders'))->assertOk();
});

it('allows bar_staff to access kitchen orders', function () {
    actingAsRole('bar_staff')->get(route('kitchen-orders'))->assertOk();
});

it('denies server access to kitchen orders', function () {
    actingAsRole('server')->get(route('kitchen-orders'))->assertForbidden();
});

it('denies maintenance_crew access to kitchen orders', function () {
    actingAsRole('maintenance_crew')->get(route('kitchen-orders'))->assertForbidden();
});

// ── Dishes: management, chef, bar_staff ──

it('allows management to access dishes', function () {
    actingAsRole('management')->get(route('dishes'))->assertOk();
});

it('allows chef to access dishes', function () {
    actingAsRole('chef')->get(route('dishes'))->assertOk();
});

it('allows bar_staff to access dishes', function () {
    actingAsRole('bar_staff')->get(route('dishes'))->assertOk();
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
