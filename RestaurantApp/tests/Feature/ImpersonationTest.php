<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    (new RoleSeeder)->run();
});

function makeAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('management');

    return $user;
}

function makeRegularUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('chef');

    return $user;
}

// ── Start impersonation ──────────────────────────────────────────

it('allows an admin to start impersonating a non-admin user', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();

    $this->actingAs($admin)
        ->post(route('impersonation.start', $target))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('impersonation.original_user_id', $admin->id)
        ->assertSessionHas('impersonation.user_id', $target->id);
});

it('swaps the auth context to the impersonated user on subsequent requests', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();

    $this->actingAs($admin)
        ->post(route('impersonation.start', $target));

    $this->withSession([
        'impersonation.original_user_id' => $admin->id,
        'impersonation.user_id' => $target->id,
    ])->get(route('dashboard'))->assertOk();

    expect(Auth::id())->toBe($target->id);
});

it('rejects a non-admin attempting to start impersonation', function () {
    $nonAdmin = makeRegularUser();
    $target = makeRegularUser();

    $this->actingAs($nonAdmin)
        ->post(route('impersonation.start', $target))
        ->assertForbidden();
});

it('rejects impersonating an admin user', function () {
    $admin = makeAdmin();
    $anotherAdmin = makeAdmin();

    $this->actingAs($admin)
        ->post(route('impersonation.start', $anotherAdmin))
        ->assertForbidden();
});

it('rejects an admin from impersonating themselves', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->post(route('impersonation.start', $admin))
        ->assertForbidden();
});

it('overwrites an existing impersonation session when starting a new one', function () {
    $admin = makeAdmin();
    $firstTarget = makeRegularUser();
    $secondTarget = makeRegularUser();

    $this->actingAs($admin)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $firstTarget->id,
        ])
        ->post(route('impersonation.start', $secondTarget))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('impersonation.original_user_id', $admin->id)
        ->assertSessionHas('impersonation.user_id', $secondTarget->id);
});

it('allows an already-impersonating admin to start impersonating another user', function () {
    $admin = makeAdmin();
    $firstTarget = makeRegularUser();
    $secondTarget = makeRegularUser();

    // Admin is currently impersonating firstTarget (Auth::user() returns firstTarget)
    $this->actingAs($firstTarget)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $firstTarget->id,
        ])
        ->post(route('impersonation.start', $secondTarget))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('impersonation.original_user_id', $admin->id)
        ->assertSessionHas('impersonation.user_id', $secondTarget->id);
});

// ── Stop impersonation ──────────────────────────────────────────

it('allows stopping an active impersonation session', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();

    $this->actingAs($admin)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $target->id,
        ])
        ->delete(route('impersonation.stop'))
        ->assertRedirect(route('accounts.index'))
        ->assertSessionMissing('impersonation.original_user_id')
        ->assertSessionMissing('impersonation.user_id');
});

it('rejects stopping impersonation when no session is active', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->delete(route('impersonation.stop'))
        ->assertForbidden();
});

it('allows stopping impersonation when the impersonated user was deleted', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();
    $deletedTargetId = $target->id;

    $target->delete();

    $this->actingAs($admin)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $deletedTargetId,
        ])
        ->delete(route('impersonation.stop'))
        ->assertRedirect(route('accounts.index'))
        ->assertSessionMissing('impersonation.original_user_id')
        ->assertSessionMissing('impersonation.user_id');
});

it('allows stopping impersonation when the impersonated user became an admin', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();

    $target->syncRoles(['management']);

    $this->actingAs($admin)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $target->id,
        ])
        ->delete(route('impersonation.stop'))
        ->assertRedirect(route('accounts.index'))
        ->assertSessionMissing('impersonation.original_user_id')
        ->assertSessionMissing('impersonation.user_id');
});

it('allows stopping impersonation when the original admin lost their admin role', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();

    $admin->syncRoles(['chef']);

    $this->actingAs($admin)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $target->id,
        ])
        ->delete(route('impersonation.stop'))
        ->assertRedirect(route('accounts.index'))
        ->assertSessionMissing('impersonation.original_user_id')
        ->assertSessionMissing('impersonation.user_id');
});

// ── Middleware safety checks ─────────────────────────────────────

it('clears the session and continues as the real user when the original admin no longer exists', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();
    $deletedAdminId = $admin->id;

    $admin->delete();

    $this->actingAs($target)
        ->withSession([
            'impersonation.original_user_id' => $deletedAdminId,
            'impersonation.user_id' => $target->id,
        ])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionMissing('impersonation.original_user_id');
});

it('clears the session and continues as the real user when the original admin loses admin role', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();

    $admin->syncRoles(['chef']);

    $this->actingAs($admin)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $target->id,
        ])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionMissing('impersonation.original_user_id');
});

it('clears the session and continues as the real user when the impersonated user no longer exists', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();
    $deletedTargetId = $target->id;

    $target->delete();

    $this->actingAs($admin)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $deletedTargetId,
        ])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionMissing('impersonation.original_user_id');
});

it('clears the session and continues as the real user when the impersonated user becomes an admin', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();

    $target->syncRoles(['management']);

    $this->actingAs($admin)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $target->id,
        ])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionMissing('impersonation.original_user_id');
});

// ── Write isolation ──────────────────────────────────────────────

it('rolls back database writes made during an impersonation request', function () {
    $admin = makeAdmin();
    $target = makeRegularUser();
    $originalName = $target->name;

    // Make a request that writes to the DB while impersonating — the update should be rolled back
    $this->actingAs($target)
        ->withSession([
            'impersonation.original_user_id' => $admin->id,
            'impersonation.user_id' => $target->id,
        ])
        ->patch(route('profile.update'), [
            'name' => 'Changed Name',
            'email' => $target->email,
        ]);

    expect($target->fresh()->name)->toBe($originalName);
});
