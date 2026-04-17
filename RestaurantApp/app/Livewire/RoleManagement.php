<?php

namespace App\Livewire;

use App\Models\Role;
use App\Support\PermissionRegistry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Title('Account Management')]
class RoleManagement extends Component
{
    /** @var array<string, string> */
    public const array COLORS = [
        'purple' => 'bg-purple-500',
        'blue' => 'bg-blue-500',
        'teal' => 'bg-teal-500',
        'green' => 'bg-green-500',
        'amber' => 'bg-amber-500',
        'orange' => 'bg-orange-500',
        'rose' => 'bg-rose-500',
        'red' => 'bg-red-500',
        'indigo' => 'bg-indigo-500',
        'cyan' => 'bg-cyan-500',
        'pink' => 'bg-pink-500',
        'slate' => 'bg-slate-500',
    ];

    public ?int $selectedRoleId = null;

    public bool $showRoleForm = false;

    public ?int $editingRoleId = null;

    public string $formName = '';

    public string $formColor = 'slate';

    public ?int $deleteConfirmRoleId = null;

    public string $successMessage = '';

    public function mount(): void
    {
        $roles = Role::orderBy('name')->get();

        if ($roles->isNotEmpty()) {
            $this->selectedRoleId = $roles->first()->id;
        }
    }

    #[Computed]
    public function roles(): Collection
    {
        return Role::orderBy('name')->get();
    }

    #[Computed]
    public function selectedRole(): ?Role
    {
        if ($this->selectedRoleId === null) {
            return null;
        }

        return Role::find($this->selectedRoleId);
    }

    /**
     * Returns permissions for the selected role, keyed by name.
     *
     * @return array<string, bool>
     */
    #[Computed]
    public function rolePermissions(): array
    {
        if ($this->selectedRole === null) {
            return [];
        }

        return $this->selectedRole->permissions->pluck('name')->flip()->map(fn () => true)->all();
    }

    public function selectRole(int $id): void
    {
        $this->selectedRoleId = $id;
        $this->showRoleForm = false;
        $this->successMessage = '';
    }

    public function openCreateForm(): void
    {
        $this->editingRoleId = null;
        $this->formName = '';
        $this->formColor = 'slate';
        $this->showRoleForm = true;
        $this->successMessage = '';
        $this->resetErrorBag();
    }

    public function openEditForm(int $id): void
    {
        $role = Role::findOrFail($id);

        $this->editingRoleId = $id;
        $this->formName = $role->name;
        $this->formColor = $role->color;
        $this->showRoleForm = true;
        $this->successMessage = '';
        $this->resetErrorBag();
    }

    public function cancelRoleForm(): void
    {
        $this->showRoleForm = false;
        $this->resetErrorBag();
    }

    public function saveRole(): void
    {
        $this->authorize('Manage Roles');

        $rules = [
            'formName' => ['required', 'string', 'max:255'],
            'formColor' => ['required', 'string', 'in:'.implode(',', array_keys(self::COLORS))],
        ];

        if ($this->editingRoleId === null) {
            $rules['formName'][] = 'unique:roles,name';
        } else {
            $rules['formName'][] = Rule::unique('roles', 'name')->ignore($this->editingRoleId);
        }

        $this->validate($rules, [], [
            'formName' => 'name',
            'formColor' => 'color',
        ]);

        if ($this->editingRoleId !== null) {
            $role = Role::findOrFail($this->editingRoleId);

            // Prevent renaming the Management role
            if ($role->name === 'management' && $this->formName !== 'management') {
                $this->addError('formName', 'The Management role name cannot be changed.');

                return;
            }

            $role->update(['name' => $this->formName, 'color' => $this->formColor]);
            $this->successMessage = "Role \"{$role->name}\" has been updated.";
        } else {
            $role = Role::create([
                'name' => $this->formName,
                'color' => $this->formColor,
                'guard_name' => 'web',
                'is_administrator' => false,
            ]);

            $this->selectedRoleId = $role->id;
            $this->successMessage = "Role \"{$role->name}\" has been created.";
        }

        $this->showRoleForm = false;
    }

    public function toggleAdministrator(): void
    {
        $this->authorize('Manage Roles');

        $role = $this->selectedRole;

        if ($role === null) {
            return;
        }

        // The Management role's administrator toggle is permanently locked on
        if ($role->name === 'management') {
            return;
        }

        $newValue = ! $role->is_administrator;
        $role->update(['is_administrator' => $newValue]);
        $this->successMessage = $newValue
            ? "Administrator enabled for \"{$role->name}\"."
            : "Administrator disabled for \"{$role->name}\".";
    }

    public function togglePermission(string $permissionName): void
    {
        $this->authorize('Manage Roles');

        $role = $this->selectedRole;

        if ($role === null || $role->is_administrator) {
            return;
        }

        if (! in_array($permissionName, PermissionRegistry::allNames(), true)) {
            return;
        }

        // Use already-loaded rolePermissions to avoid findByName() throwing
        // PermissionDoesNotExist when the database has not been seeded yet.
        if (isset($this->rolePermissions[$permissionName])) {
            $role->revokePermissionTo($permissionName); // safe: permission is granted, so it exists in DB

            // If disabling a view gate, auto-revoke all action permissions it guards
            if (PermissionRegistry::isViewGate($permissionName)) {
                foreach (PermissionRegistry::permissionsGatedBy($permissionName) as $gatedName) {
                    if (isset($this->rolePermissions[$gatedName])) {
                        $role->revokePermissionTo($gatedName); // safe: same reasoning
                    }
                }
            }
        } else {
            // Cannot enable a gated permission when its view gate is off
            $viewGate = PermissionRegistry::viewGateFor($permissionName);

            if ($viewGate !== null && ! isset($this->rolePermissions[$viewGate])) {
                return;
            }

            // Use first() instead of findByName() to avoid throwing when the
            // permission has not been seeded into the database yet.
            $permission = Permission::where('name', $permissionName)->where('guard_name', 'web')->first();

            if ($permission === null) {
                return;
            }

            $role->givePermissionTo($permission);
        }

        // Bust the memoized cache so the view re-computes from the updated relationship.
        unset($this->rolePermissions);

        $this->successMessage = '';
    }

    public function confirmDelete(int $id): void
    {
        // Cannot delete the Management role
        $role = Role::findOrFail($id);
        if ($role->name === 'management') {
            return;
        }

        $this->deleteConfirmRoleId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteConfirmRoleId = null;
    }

    public function deleteRole(): void
    {
        $this->authorize('Manage Roles');

        if ($this->deleteConfirmRoleId === null) {
            return;
        }

        $role = Role::findOrFail($this->deleteConfirmRoleId);

        // Final guard: cannot delete management
        if ($role->name === 'management') {
            $this->deleteConfirmRoleId = null;

            return;
        }

        $name = $role->name;
        $role->delete(); // cascade removes from users via Spatie's foreignKey cascade

        $deletedId = $role->id;
        $this->deleteConfirmRoleId = null;
        $this->successMessage = "Role \"{$name}\" has been deleted.";

        // If the deleted role was selected, select the first remaining role
        if ($this->selectedRoleId === $deletedId) {
            $this->selectedRoleId = Role::orderBy('name')->first()?->id;
        }
    }

    public function render(): View
    {
        return view('livewire.role-management');
    }
}
