---
description: RBAC permissions overhaul — audit findings and recommended changes
---

# Permissions Overhaul

Audit of the full codebase against the current `PermissionRegistry`, `RoleSeeder`, routes, Livewire components, and nav. Below are all identified gaps, inconsistencies, and recommended changes.

---

## Findings

### 1. Dishes mutation routes use `role:` middleware instead of `permission:`

**Files:** `routes/web.php` lines 87–97

```php
Route::post('/dishes', ...)->middleware('role:management|chef|bar_staff');
Route::post('/dishes/{dish}/update', ...)->middleware('role:management|chef|bar_staff');
Route::delete('/dishes/{dish}', ...)->middleware('role:management|chef|bar_staff');
```

These three routes bypass the permission system entirely and hardcode role names. `Add Dishes`, `Edit Dishes`, and `Delete Dishes` exist in `PermissionRegistry` but are **never enforced on any route or Livewire action**. Any custom role with `View Dishes` can create/edit/delete dishes because `DishSheet.php` has no `authorize()` calls.

**Recommendation:** Replace `role:` middleware with `permission:Add Dishes`, `permission:Edit Dishes`, `permission:Delete Dishes`. Add `$this->authorize('Add Dishes')` / `$this->authorize('Edit Dishes')` / `$this->authorize('Delete Dishes')` inside `DishSheet::save()` and `DishSheet::deleteDish()`.

---

### 2. Maintenance mutation routes use `View Maintenance` instead of dedicated action permissions

**File:** `routes/web.php` lines 107–113

```php
Route::patch('/maintenance/{task}/notes', ...)->middleware('permission:View Maintenance');
Route::patch('/maintenance/{task}/done',  ...)->middleware('permission:View Maintenance');
```

Anyone who can **view** the maintenance page can also edit notes and mark tasks as done. These are write operations and should require separate permissions.

**Recommendation:** Add two new permissions to `PermissionRegistry` under the `maintenance` group:

| Permission | Description |
|---|---|
| `Edit Maintenance Task` | Edit notes on a maintenance task |
| `Complete Maintenance Task` | Mark a maintenance task as done |

Update `maintenance.updateNotes` to require `permission:Edit Maintenance Task`.
Update `maintenance.markAsDone` to require `permission:Complete Maintenance Task`.
Update `RoleSeeder` to grant both to `maintenance_crew`.

---

### 3. Account mutation routes use a single `View Account Management` gate

**File:** `routes/web.php` lines 39–41

```php
Route::resource('accounts', AccountController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware('permission:View Account Management');
```

`store`, `update`, and `destroy` are protected only by `View Account Management`. `Create User`, `Edit User`, `Delete User`, and `Manage Roles` exist in `PermissionRegistry` but are **never enforced on any route**. A custom role with only `View Account Management` can create, update, and delete accounts.

**Recommendation:** Split the resource middleware per action:

| Route | Required Permission |
|---|---|
| `accounts.index` | `View Account Management` |
| `accounts.store` | `Create User` |
| `accounts.update` | `Edit User` |
| `accounts.destroy` | `Delete User` |

`Manage Roles` should be enforced inside `RoleManagement` Livewire component actions (create, update, delete role, sync permissions).

---

### 4. `DishSheet` Livewire component has zero authorization

**File:** `app/Livewire/Dishes/DishSheet.php`

`save()` creates or updates a dish. `deleteDish()` deletes a dish. `createIngredient()` creates an ingredient. None call `$this->authorize()`. Any authenticated user who can reach the Livewire endpoint can call these actions directly.

**Recommendation:**
- `save()` — add `$this->authorize($this->dishId ? 'Edit Dishes' : 'Add Dishes');`
- `deleteDish()` — add `$this->authorize('Delete Dishes');`
- `createIngredient()` — gated by `Edit Dishes` (ingredient creation is part of dish editing).

---

### 5. `TableManagement` Livewire component has zero authorization

**File:** `app/Livewire/TableManagement.php`

The route is protected by `View Table Management`, but all write actions inside the component (enter edit mode, move/resize elements, create/delete floor plans, upload images, update table status) have no `$this->authorize()` calls. Any role with `View Table Management` can perform all write operations.

Permissions `Edit Table Layout`, `Manage Floor Plans`, and `Update Table Status` exist in `PermissionRegistry` but are only used in the role editor UI — not enforced anywhere in code.

**Recommendation:** Add `$this->authorize()` calls to the relevant Livewire action methods:

| Action | Required Permission |
|---|---|
| Enter edit mode / save layout | `Edit Table Layout` |
| Create / delete floor plan, upload image | `Manage Floor Plans` |
| Update table status (outside edit mode) | `Update Table Status` or `Manage Availability` |

---

### 6. `Reservations` Livewire component — cancel action not checked

**File:** `routes/web.php` line 79–81; `app/Livewire/Reservations.php`

`PUT /reservations/{reservation}` requires `Edit Reservation`. `PATCH /reservations/{reservation}/status` also requires `Edit Reservation`. However, `Cancel Reservation` permission exists but is **not enforced on any route or component action**. The cancel flow goes through the status route, which only checks `Edit Reservation`.

**Recommendation:** Either enforce `Cancel Reservation` on a dedicated cancel route/action, or document explicitly that `Edit Reservation` covers status changes including cancellation and remove `Cancel Reservation` as a redundant permission.

---

### 7. `Manage Availability` permission is defined but never enforced

**File:** `PermissionRegistry.php` — `general` group

`Manage Availability` is described as "Toggle dish availability on the Dishes page; update table status on Table Management." Neither the dishes availability toggle nor the table status update checks for this permission in code. It is granted to no role in `RoleSeeder`.

**Recommendation:** Either enforce it in the relevant Livewire actions (`DishesPage` availability toggle, `TableManagement` status update as an alternative to `Update Table Status`), or remove it from the registry if the overlap with `Update Table Status` is undesirable.

---

### 8. `Export Data` permission is defined but never enforced

**File:** `PermissionRegistry.php` — `general` group

`Export Data` is granted to no role in `RoleSeeder` and no route or controller checks for it.

**Recommendation:** Enforce on any CSV export routes/actions, or remove if export functionality does not exist yet.

---

### 9. Navigation shows Maintenance link to all authenticated users

**File:** `resources/views/layouts/navigation.blade.php` lines 75–78, 212–215

```blade
{{-- Maintenance --}}
<x-nav-link :href="route('maintenance')" ...>
    {{ __('Maintenance') }}
</x-nav-link>
```

Every other nav section is wrapped in `@can`. The Maintenance link is **unconditionally rendered** for all logged-in users, even though the route is permission-gated. Users without `View Maintenance` see the link but get a 403 when they click it.

**Recommendation:** Wrap in `@can('View Maintenance')` on both desktop and mobile nav — matching every other link's pattern.

---

### 10. `Bar Orders` missing from `permission-specific.md` view-gate list

**File:** `.windsurf/workflows/permission-specific.md` line 106

> Pages with a View gate: Dishes, Kitchen Orders, Orders, Account Management, Table Management, Statistics.

`Bar Orders`, `Reservations`, and `Maintenance` all have `view_gate` set in `PermissionRegistry` and route middleware protecting them — but they are missing from this documentation list.

**Recommendation:** Update the spec to include all pages with a view gate.

---

### 11. `RoleSeeder` — `maintenance_crew` has `Edit Table Layout` and `Manage Floor Plans`

**File:** `database/seeders/RoleSeeder.php` lines 62–63

```php
'View Table Management', 'Edit Table Layout', 'Manage Floor Plans',
```

Maintenance crew being able to edit the floor plan layout and manage floor plans (create/delete floor plans, upload images) is a high-privilege action that likely belongs to management only. Maintenance crew needing to *view* the floor plan to navigate the building is reasonable; editing it is not.

**Recommendation:** Remove `Edit Table Layout` and `Manage Floor Plans` from `maintenance_crew`. Keep `View Table Management`.

---

## Summary Table

| # | Issue | Action |
|---|---|---|
| 1 | Dishes mutation routes use `role:` not `permission:` | Replace with `permission:` middleware + Livewire `authorize()` |
| 2 | Maintenance write routes use `View Maintenance` | Add `Edit Maintenance Task` + `Complete Maintenance Task` permissions |
| 3 | Account mutations all gated by `View Account Management` | Per-action middleware for `store`/`update`/`destroy` |
| 4 | `DishSheet` has no authorization | Add `authorize()` to `save()`, `deleteDish()`, `createIngredient()` |
| 5 | `TableManagement` has no authorization | Add `authorize()` to write actions |
| 6 | `Cancel Reservation` never enforced | Enforce or remove |
| 7 | `Manage Availability` never enforced | Enforce or remove |
| 8 | `Export Data` never enforced | Enforce or remove |
| 9 | Maintenance nav link unguarded | Wrap in `@can('View Maintenance')` |
| 10 | `permission-specific.md` view-gate list incomplete | Update docs |
| 11 | `maintenance_crew` has floor plan edit permissions | Remove `Edit Table Layout`, `Manage Floor Plans` from role |
