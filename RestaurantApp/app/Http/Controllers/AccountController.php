<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Tailwind class sets for each named color used on roles.
     *
     * @var array<string, array{bg: string, text: string, dot: string}>
     */
    private const array COLOR_CLASSES = [
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'dot' => 'bg-purple-500'],
        'blue' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'dot' => 'bg-blue-500'],
        'teal' => ['bg' => 'bg-teal-100',   'text' => 'text-teal-700',   'dot' => 'bg-teal-500'],
        'green' => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'dot' => 'bg-green-500'],
        'amber' => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'dot' => 'bg-amber-500'],
        'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'dot' => 'bg-orange-500'],
        'rose' => ['bg' => 'bg-rose-100',   'text' => 'text-rose-700',   'dot' => 'bg-rose-500'],
        'red' => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'dot' => 'bg-red-500'],
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'dot' => 'bg-indigo-500'],
        'cyan' => ['bg' => 'bg-cyan-100',   'text' => 'text-cyan-700',   'dot' => 'bg-cyan-500'],
        'pink' => ['bg' => 'bg-pink-100',   'text' => 'text-pink-700',   'dot' => 'bg-pink-500'],
        'slate' => ['bg' => 'bg-slate-100',  'text' => 'text-slate-700',  'dot' => 'bg-slate-500'],
    ];

    public function index(): View
    {
        $users = User::query()->with('roles')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        $roleConfig = $roles->mapWithKeys(function (Role $role): array {
            $classes = self::COLOR_CLASSES[$role->color] ?? self::COLOR_CLASSES['slate'];

            return [
                $role->name => array_merge($classes, [
                    'label' => ucwords(str_replace(['_', '-'], ' ', $role->name)),
                ]),
            ];
        })->all();

        $counts = ['all' => $users->count()];
        foreach ($roles as $role) {
            $counts[$role->name] = $users->filter(fn ($u) => $u->hasRole($role->name))->count();
        }

        return view('accounts', [
            'users' => $users,
            'roles' => $roles,
            'roleConfig' => $roleConfig,
            'counts' => $counts,
            'activeTab' => request('tab', 'users'),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $user->syncRoles($request->validated('roles'));

        return redirect()->route('accounts.index')->with('success', "Account for \"{$user->name}\" has been created.");
    }

    public function update(UpdateAccountRequest $request, User $account): RedirectResponse
    {
        $roles = $request->validated('roles');

        // Prevent the current user from removing themselves from the Management role
        if ($account->id === Auth::id() && $account->hasRole('management') && ! in_array('management', $roles)) {
            return redirect()->route('accounts.index')
                ->with('error', 'You cannot remove the Management role from your own account.');
        }

        $account->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ]);

        if ($request->filled('password')) {
            $account->password = $request->validated('password');
        }

        $account->save();
        $account->syncRoles($roles);

        return redirect()->route('accounts.index')->with('success', "Account for \"{$account->name}\" has been updated.");
    }

    public function destroy(User $account): RedirectResponse
    {
        $this->authorize('Delete User');

        // Prevent self-deletion
        if ($account->id === Auth::id()) {
            return redirect()->route('accounts.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $name = $account->name;
        $account->delete();

        return redirect()->route('accounts.index')->with('success', "Account for \"{$name}\" has been deleted.");
    }
}
