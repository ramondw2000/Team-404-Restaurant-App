<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountController extends Controller
{
    /** @var array<string, array<string, string>> */
    private array $roleConfig = [
        'management' => ['label' => 'Management',   'bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'dot' => 'bg-purple-500'],
        'server' => ['label' => 'Server',       'bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'dot' => 'bg-blue-500'],
        'chef' => ['label' => 'Chef',          'bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'dot' => 'bg-orange-500'],
        'receptionist' => ['label' => 'Receptionist', 'bg' => 'bg-green-100',  'text' => 'text-green-700',  'dot' => 'bg-green-500'],
    ];

    public function index(): View
    {
        $users = User::query()->with('roles')->orderBy('name')->get();

        $counts = [
            'all'          => $users->count(),
            'management'   => $users->filter(fn($u) => $u->hasRole('management'))->count(),
            'server'       => $users->filter(fn($u) => $u->hasRole('server'))->count(),
            'chef'         => $users->filter(fn($u) => $u->hasRole('chef'))->count(),
            'receptionist' => $users->filter(fn($u) => $u->hasRole('receptionist'))->count(),
        ];

        return view('accounts', [
            'users' => $users,
            'roleConfig' => $this->roleConfig,
            'counts' => $counts,
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
        $account->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ]);

        if ($request->filled('password')) {
            $account->password = $request->validated('password');
        }

        $account->save();
        $account->syncRoles($request->validated('roles'));

        return redirect()->route('accounts.index')->with('success', "Account for \"{$account->name}\" has been updated.");
    }

    public function destroy(User $account): RedirectResponse
    {
        $name = $account->name;
        $account->delete();

        return redirect()->route('accounts.index')->with('success', "Account for \"{$name}\" has been deleted.");
    }
}
