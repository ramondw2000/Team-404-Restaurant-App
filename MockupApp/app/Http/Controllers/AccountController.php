<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    private array $roleConfig = [
        'management'   => ['label' => 'Management',   'bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'dot' => 'bg-violet-500'],
        'server'       => ['label' => 'Server',        'bg' => 'bg-sky-100',    'text' => 'text-sky-700',    'dot' => 'bg-sky-500'],
        'chef'         => ['label' => 'Chef',          'bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'dot' => 'bg-amber-500'],
        'receptionist' => ['label' => 'Receptionist',  'bg' => 'bg-emerald-100','text' => 'text-emerald-700','dot' => 'bg-emerald-500'],
    ];

    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('accounts', [
            'users'      => $users,
            'roleConfig' => $this->roleConfig,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['management', 'server', 'chef', 'receptionist'])],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return redirect()->route('accounts.index')->with('success', "Account for \"{$validated['name']}\" created successfully.");
    }

    public function update(Request $request, User $account): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($account->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['management', 'server', 'chef', 'receptionist'])],
        ]);

        $account->name  = $validated['name'];
        $account->email = $validated['email'];
        $account->role  = $validated['role'];

        if (!empty($validated['password'])) {
            $account->password = Hash::make($validated['password']);
        }

        $account->save();

        return redirect()->route('accounts.index')->with('success', "Account for \"{$account->name}\" updated successfully.");
    }

    public function destroy(User $account): RedirectResponse
    {
        $name = $account->name;
        $account->delete();

        return redirect()->route('accounts.index')->with('success', "Account for \"{$name}\" deleted successfully.");
    }
}
