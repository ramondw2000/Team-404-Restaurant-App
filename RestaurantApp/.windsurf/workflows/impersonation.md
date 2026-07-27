# Impersonation System

## Overview

Administrators can temporarily act as any non-administrator user directly from the Account Management page. While impersonating, the admin navigates with the impersonated user's permissions and sees their exact view — the only additions are a persistent banner in the navigation dropdown and a "Stop Impersonating" action. All database writes made during an impersonation session are silently rolled back on every request; nothing persists. The impersonation state lives entirely in the server-side session and is cleared automatically when the session expires.

---

## Scope & Constraints

- **Who can impersonate:** Users whose role has `is_administrator = true`.
- **Who can be impersonated:** Non-administrator users only. Admin-role users are excluded entirely — the button is hidden, not disabled.
- **Cannot impersonate self:** The original admin's own account is excluded from the impersonation button.
- **Write isolation:** Every request during impersonation is wrapped in a DB transaction that is always rolled back after the response is sent. Writes appear to succeed within the request cycle (the UI reacts normally) but nothing is persisted.
- **Switching:** Starting a new impersonation while already impersonating replaces the current target immediately — no explicit stop required.
- **Session expiry:** If the session expires mid-impersonation, the user is simply logged out. On next login the admin returns to their own account; no cleanup required.
- **No logging:** Impersonation events are not recorded anywhere.
- **Session driver requirement:** The session driver must be `file`, `cookie`, or `redis` — **not** `database` — so that session writes fall outside the per-request DB rollback. Document this in the project README.

---

## Session Schema

Two keys are stored in the PHP session (never in the database):

| Key | Type | Description |
|---|---|---|
| `impersonation.original_user_id` | `int` | ID of the admin who initiated the session |
| `impersonation.user_id` | `int` | ID of the user currently being impersonated |

Impersonation is considered **active** when `impersonation.original_user_id` is present in the session. Both keys are written atomically on start and removed atomically on stop.

---

## Write Isolation Mechanism

A terminating middleware wraps every impersonation request in a DB transaction that is unconditionally rolled back after the response has been sent to the browser.

```
request arrives
  → HandleImpersonation::handle()
      → switch Auth::setUser() to impersonated user
      → DB::beginTransaction()
  → controller / Livewire action executes
      → writes appear to succeed; UI reflects changes
  → response sent to browser
  → HandleImpersonation::terminate()
      → DB::rollBack()   ← all writes discarded silently
```

Because the session uses the file driver, session writes (including clearing impersonation keys on stop) are unaffected by the database rollback.

> **Note on the Spatie permission cache:** if the application cache driver is `database`, any `forgetCachedPermissions()` calls inside the transaction are also rolled back. The cache regenerates on the next request — low impact, but worth noting if unexpected permission latency appears.

---

## New Files

### `app/Http/Middleware/HandleImpersonation.php`

Runs on every `web` request, after `StartSession` and `Authenticate` in `bootstrap/app.php`.

**`handle()`**
1. If `impersonation.original_user_id` is not in session → pass through, no-op.
2. Resolve the original admin from the session ID. If the user no longer exists or no longer holds an administrator role, **clear both session keys and continue as the real authenticated user** — no abort, no redirect.
3. Resolve the impersonated user from `impersonation.user_id`. Apply the same safety check: if missing or now an administrator, clear session and continue as real user.
4. Call `Auth::setUser($impersonatedUser)` to swap the auth context for the remainder of the request.
5. Call `DB::beginTransaction()`.

**`terminate()`**
1. If impersonation was active during this request, call `DB::rollBack()`.

### `app/Http/Controllers/ImpersonationController.php`

**`start(Request $request, User $target)`** — `POST /accounts/impersonate/{user}`

1. Resolve the **acting admin**: if already impersonating, read `session('impersonation.original_user_id')` and look up that user; otherwise use `Auth::user()`. This ensures an already-impersonating admin (whose `Auth::user()` is the impersonated account) can still start a new impersonation.
2. Abort 403 if the acting admin does not have `is_administrator = true`.
3. Abort 403 if `$target` has `is_administrator = true`.
4. Abort 403 if `$target->id === $actingAdmin->id`.
5. Write `impersonation.original_user_id` and `impersonation.user_id` to session (overwrites any existing impersonation).
6. Redirect to `route('dashboard')`.

**`stop(Request $request)`** — `DELETE /accounts/impersonate`

1. Abort 403 if `session('impersonation.original_user_id')` is not set.
2. Forget both session keys.
3. Redirect to `route('accounts.index')`.

---

## Routes

```php
Route::middleware('auth')->group(function () {
    Route::post('/accounts/impersonate/{user}', [ImpersonationController::class, 'start'])
        ->name('impersonation.start');

    Route::delete('/accounts/impersonate', [ImpersonationController::class, 'stop'])
        ->name('impersonation.stop');
});
```

These routes sit inside the `auth` group but are **not** behind the `permission:View Account Management` middleware. The `stop` route must be reachable from any page regardless of the impersonated user's permissions.

---

## UI Changes

### 1. User row — start button

**File:** `resources/views/components/accounts/user-row.blade.php`

Add a `POST` form button to the existing actions column, alongside Edit and Delete. Apply the following visibility rules in Blade:

```
@php
    $originalAdminId = session('impersonation.original_user_id');
    $actor = $originalAdminId
        ? \App\Models\User::find($originalAdminId)
        : Auth::user();
    $canImpersonate = $actor
        && $actor->roles()->where('is_administrator', true)->exists()
        && ! $rowUser->roles()->where('is_administrator', true)->exists()
        && $rowUser->id !== $actor->id;
@endphp

@if($canImpersonate)
    <form method="POST" action="{{ route('impersonation.start', $rowUser) }}">
        @csrf
        <button type="submit" title="Impersonate {{ $rowUser->name }}">
            <!-- person-with-arrow icon -->
        </button>
    </form>
@endif
```

The button must be **completely absent** from the DOM for admin-role users (not just disabled), to prevent visual confusion and accidental misuse.

### 2. Navigation — banner and stop button

**File:** `resources/views/layouts/navigation.blade.php`

Check `session('impersonation.original_user_id')` to determine whether to render impersonation UI. No auth calls are needed — the session key is authoritative.

**Trigger button** — add a small amber badge inline with the user name:

```
[ ⚠ Impersonating ]  Chef Name  ▾
```

**Dropdown content** — insert a section at the top, above the Profile link, separated by a divider:

```
┌──────────────────────────────────────┐
│  ⚠  Impersonating                    │
│     Chef Name  ·  Chef               │  ← name + role(s)
│  [ Stop Impersonating ]              │  ← styled as a danger/warning action
├──────────────────────────────────────┤
│  Profile                             │
│  Log Out                             │
└──────────────────────────────────────┘
```

"Stop Impersonating" submits a `DELETE` form to `route('impersonation.stop')`. Style it distinctly (amber or red text) so it reads as a session control, not a navigation link.

The responsive hamburger menu must mirror the same addition.

---

## Authorization Summary

No dedicated Policy class. All checks live in the controller and middleware, keeping the surface small.

| Check | Enforced in |
|---|---|
| Viewer is admin (or original admin while impersonating) | `ImpersonationController@start` |
| Target is not admin | `ImpersonationController@start` |
| Target is not the original admin | `ImpersonationController@start` |
| Active session required before stopping | `ImpersonationController@stop` |
| Original admin still exists & still is admin | `HandleImpersonation` middleware (per request) |
| Impersonated user still exists & still is non-admin | `HandleImpersonation` middleware (per request) |

---

## Edge Cases

| Scenario | Behaviour |
|---|---|
| Impersonated user is deleted mid-session | Middleware detects missing user, clears session keys, request continues as the real admin |
| Original admin loses their administrator role mid-session | Middleware detects lost `is_administrator`, clears session keys, request continues as the real admin |
| Admin impersonates user B while already impersonating user A | `start()` overwrites both session keys; no explicit stop needed |
| Admin navigates to Account Management while impersonating | They see it only if the impersonated user has `View Account Management` — otherwise the route is 403, as expected |
| `database` session driver | Session saves are inside the DB transaction; rolling back destroys session state. Must document and enforce `file`/`redis` driver |
| Spatie cache with `database` cache driver | `forgetCachedPermissions()` inside the transaction is rolled back; cache self-heals on next request. No action needed |

---

## Out of Scope

- Audit logging of impersonation events
- Time-limited impersonation sessions (auto-expiry after N minutes)
- Restricting specific actions (e.g. blocking password changes) during impersonation — the DB rollback handles write isolation globally
