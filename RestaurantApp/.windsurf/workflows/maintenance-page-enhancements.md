---
description: Maintenance Page — Assignment, Workflow Status & Requirements Checklist
---

# Maintenance Page Enhancements

## Overview
Extend the existing maintenance tasks page with user assignment, a 3-stage workflow (Assigned → In Progress → Done), and a rich-text checklist for completion requirements.

## Database Changes

### Migration: Update maintenance_tasks table
- Add `assigned_to` (nullable foreign key → users.id, cascade on delete, set null on user delete with fallback to "Nonexistent User" display)
- Rename `status` values: migrate existing 'pending' → 'assigned', 'completed' → 'done'
- Add `requirements` (nullable JSON) — stores checklist array: `[{id, content, is_completed, sort_order}]`
- Add index on `assigned_to` for filtering performance

### Status Enum (MaintenanceTaskStatus)
Replace existing Pending/Completed with:
- `Assigned` — task created but work not started
- `InProgress` — assignee has started working
- `Done` — task completed (was 'completed', migrate data)

## Permission Model

### New Permission
- `Assign Maintenance Task` — allows assigning OTHER users (self-assignment requires only `View Maintenance`)

### Assignment Rules
- **Self-assign**: Any user with `View Maintenance` can assign themselves to unassigned tasks (inline dropdown)
- **Assign others**: Requires `Assign Maintenance Task` permission
- **Unassign**: 
  - Self: users can unassign themselves from their tasks
  - Others: requires `Assign Maintenance Task` permission or admin role
- **Auto-unassign**: When a user is disabled/inactivated, all their tasks auto-unassign (set `assigned_to` null, preserve status)

### Permission Registry Update
Add to `PermissionRegistry::GROUPS['maintenance']['permissions']`:
```php
['name' => 'Assign Maintenance Task', 'description' => 'Assign other users to maintenance tasks'],
```

## UI/UX Specifications

### Assignment Interface (Inline Dropdown)
Located in task row, replaces static status display:
- **Closed state**: Shows assigned user avatar + name, or "Unassigned" badge
- **Open state**: Dropdown with searchable user list
  - Filtered to users with `View Maintenance` permission only
  - Display: avatar (32px) + full name
  - Options: "Unassign" at top (if assigned), then user list alphabetically
  - Current user highlighted with "(You)" label
- **Interaction**: Click avatar/name to open, click outside to close, Enter to select
- **Unassign action**: Red "Unassign" button in dropdown, requires confirmation for non-self unassigns

### Status Badges & Controls
- Color coding:
  - `Assigned`: amber/warning badge
  - `InProgress`: blue/info badge  
  - `Done`: green/success badge
- **Status transitions**: Inline button in row (next to badge)
  - `Assigned` → `InProgress`: "Start Work" button (visible to assignee only)
  - `InProgress` → `Done`: "Mark Done" button (visible to assignee only)
  - `Done` → `InProgress`: "Reopen" button (visible to all with Edit permission)

### Requirements Checklist (Task Detail Panel)
Livewire component: `MaintenanceTaskRequirements`
- **Location**: Expandable row section or slide-out panel
- **Display**: Inline editable list with drag-drop reordering
  - Each item: checkbox (toggle completed), rich text content (Trix/Lite editor), drag handle, delete button
  - Add item: Inline "Add requirement..." row at bottom, becomes editable text field on click
  - Reordering: Drag handle with Alpine.js sortable or Livewire drag-drop
- **Permissions**: 
  - Edit content: assignee + admins only
  - Toggle completed: assignee only
  - View: all users with `View Maintenance`
- **Persistence**: Auto-save on blur/2s debounce, show saving indicator

### List View Columns
Update `task-table.blade.php` column order:
1. Task (name)
2. Location
3. Assigned To (avatar + name, or "Unassigned")
4. Status (badge + transition button)
5. Date Created
6. Actions (Requirements toggle dropdown)

### Search & Filtering
- **Search scope**: task name, description/notes, assigned user name
- **Quick-filters** (mutually exclusive radio buttons):
  - "All Tasks" (default)
  - "My Tasks" (current user is assignee)
  - "Unassigned"
- **Status filter**: Multi-select checkboxes (Assigned, In Progress, Done)
- **Assigned user filter**: Dropdown of users with tasks assigned
- No filter persistence (no localStorage)

### Pagination
- 25 tasks per page (configurable)
- Requirements lazy-loaded per task when expanded
- Eager load `assignedUser` relationship to avoid N+1

## Livewire Components

### New Components
1. `TaskAssignmentDropdown` — inline user assignment dropdown
   - Props: `taskId`, `assignedUserId` (nullable)
   - Events: `assignmentUpdated`
   
2. `TaskStatusTransition` — status badge + transition button
   - Props: `taskId`, `currentStatus`, `isAssignee`
   - Events: `statusUpdated`
   
3. `MaintenanceTaskRequirements` — requirements checklist
   - Props: `taskId`
   - Methods: `addItem()`, `updateItem()`, `toggleCompleted()`, `deleteItem()`, `reorder()`

### Modified Components
- `task-row.blade.php` — add assignment dropdown, status transition, requirements expand
- `task-table.blade.php` — new column structure, pagination support
- `maintenance.blade.php` — add search bar, quick-filters, status filters

## Controller Changes

### MaintenanceController
Update `index()` method:
```php
// Add eager loading, pagination, filter support
$tasks = MaintenanceTask::with('assignedUser')
    ->when($request->filter === 'my-tasks', fn($q) => $q->where('assigned_to', auth()->id()))
    ->when($request->filter === 'unassigned', fn($q) => $q->whereNull('assigned_to'))
    ->when($request->status, fn($q, $s) => $q->whereIn('status', $s))
    ->when($request->search, fn($q, $s) => $q->where(function($q) use ($s) {
        $q->where('name', 'like', "%{$s}%")
          ->orWhere('notes', 'like', "%{$s}%")
          ->orWhereHas('assignedUser', fn($q) => $q->where('name', 'like', "%{$s}%"));
    }))
    ->paginate(25);
```

### New Methods
- `assign(Task $task, User $user)` — assign user (respects permissions)
- `unassign(Task $task)` — unassign current assignee
- `transitionStatus(Task $task, string $status)` — validate and update status

## Model Changes

### MaintenanceTask
```php
protected $fillable = ['name', 'location', 'status', 'notes', 'assigned_to', 'requirements'];

protected function casts(): array
{
    return [
        'status' => MaintenanceTaskStatus::class,
        'requirements' => 'array', // or AsCollection::class
    ];
}

public function assignedUser(): BelongsTo
{
    return $this->belongsTo(User::class, 'assigned_to');
}

public function isAssignee(User $user): bool
{
    return $this->assigned_to === $user->id;
}
```

## Edge Cases & Behavior

1. **Disabled user auto-unassign**: Observer on User model — when `active` set false, unassign all tasks
2. **Deleted user handling**: Foreign key with onDelete('set null'), display name falls back to "Nonexistent User"
3. **Race condition on self-assign**: Last-write-wins acceptable (no optimistic locking)
4. **Reopening Done tasks**: Requirements checklist state preserved, no auto-reset
5. **Bulk operations**: Not required for initial implementation

## Migration Strategy

1. **Data migration**: 
   - `pending` → `Assigned`
   - `completed` → `Done`
   - Update seeder to use new statuses
   
2. **Permission seeding**: Add `Assign Maintenance Task` to admin role by default

3. **Rollback plan**: 
   - Status: `Done` → `completed`, `Assigned`/`InProgress` → `pending`
   - Drop `assigned_to` and `requirements` columns

## Component Library Usage

Use existing shadcn-like components:
- `x-ui.dropdown` for assignment dropdown (extended)
- `x-ui.badge` for status badges with new variant mappings
- `x-ui.avatar` for user avatars in assignment display
- `x-ui.button` for status transition actions
- `x-ui.card` for requirements checklist container
- `x-ui.empty-state` for empty checklist state

## Non-Functional Requirements

- **No notifications**: Assignment changes do not trigger emails/toasts
- **No audit log**: Status changes and assignment history not persisted beyond current state
- **No filter persistence**: Filters reset on page reload
- **Concurrency**: Last-write-wins acceptable for all operations
- **Performance**: Lazy load requirements, paginate 25 items, eager load assignedUser

## Testing Strategy (Manual)

Not writing automated tests per direction — manual verification of:
- Self-assignment flow
- Permission enforcement (assign others requires permission)
- Status transitions (assignee-only for Assigned→InProgress→Done)
- Auto-unassign on user disable
- Search by assigned user name
- Requirements CRUD and drag-drop reordering
- Pagination with lazy-loaded requirements

## File Checklist

### Create
- `app/Livewire/TaskAssignmentDropdown.php`
- `app/Livewire/TaskStatusTransition.php`
- `app/Livewire/MaintenanceTaskRequirements.php`
- `resources/views/livewire/task-assignment-dropdown.blade.php`
- `resources/views/livewire/task-status-transition.blade.php`
- `resources/views/livewire/maintenance-task-requirements.blade.php`
- `database/migrations/2026_04_29_xxxxxx_update_maintenance_tasks_assignment.php`

### Modify
- `app/Enums/MaintenanceTaskStatus.php` — update cases
- `app/Models/MaintenanceTask.php` — add fields, casts, relationship
- `app/Http/Controllers/MaintenanceController.php` — add filtering, pagination
- `app/Support/PermissionRegistry.php` — add new permission
- `resources/views/components/maintenance/task-row.blade.php`
- `resources/views/components/maintenance/task-table.blade.php`
- `resources/views/maintenance.blade.php`
- `database/seeders/MaintenanceTaskSeeder.php` — update status values

