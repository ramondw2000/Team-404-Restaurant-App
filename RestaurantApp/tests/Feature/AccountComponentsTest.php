<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;

it('renders the accounts styles component with sheet CSS', function () {
    $html = Blade::render('<x-accounts.styles />');

    expect($html)
        ->toContain('<style>')
        ->toContain('.sheet-overlay')
        ->toContain('.sheet-panel')
        ->toContain('translateX(100%)')
        ->toContain('.sheet-panel.open');
});

it('renders the accounts scripts component with JS functions', function () {
    view()->share('errors', new ViewErrorBag);
    $html = Blade::render('<x-accounts.scripts />');

    expect($html)
        ->toContain('<script>')
        ->toContain('switchTab')
        ->toContain('openSheet')
        ->toContain('openEditSheet')
        ->toContain('closeSheet')
        ->toContain('confirmDelete')
        ->toContain('closeDelete')
        ->toContain('updateRoleDesc')
        ->toContain('roleDescriptions');
});

it('renders the accounts role-tabs component with all role tabs', function () {
    (new RoleSeeder)->run();

    $roles = Role::orderBy('name')->get();
    $counts = ['all' => 10];
    foreach ($roles as $role) {
        $counts[$role->name] = 0;
    }

    $html = Blade::render(
        '<x-accounts.role-tabs :counts="$counts" :roles="$roles" />',
        compact('counts', 'roles')
    );

    expect($html)
        ->toContain('All')
        ->toContain('Management')
        ->toContain('Server')
        ->toContain('Chef')
        ->toContain('Receptionist')
        ->toContain('Bartender')
        ->toContain('Barista')
        ->toContain('Maintenance Crew')
        ->toContain('data-role="all"')
        ->toContain('data-role="management"')
        ->toContain('data-role="server"')
        ->toContain('switchTab(this)');
});

it('renders the accounts user-table component with users and empty state', function () {
    Role::findOrCreate('management', 'web');
    $user = User::factory()->create(['name' => 'Sofia Ricci']);
    $user->assignRole('management');

    $roleConfig = [
        'management' => ['label' => 'Management', 'dot' => 'bg-blue-500', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
    ];

    $html = Blade::render(
        '<x-accounts.user-table :users="$users" :roleConfig="$roleConfig" />',
        ['users' => collect([$user]), 'roleConfig' => $roleConfig]
    );

    expect($html)
        ->toContain('Sofia Ricci')
        ->toContain('User')
        ->toContain('Email')
        ->toContain('Role')
        ->toContain('Actions')
        ->toContain('no-users');
});

it('renders the accounts user-table empty state when no users exist', function () {
    $html = Blade::render(
        '<x-accounts.user-table :users="$users" :roleConfig="$roleConfig" />',
        ['users' => collect(), 'roleConfig' => []]
    );

    expect($html)
        ->toContain('No accounts found.')
        ->toContain('no-users');
});
