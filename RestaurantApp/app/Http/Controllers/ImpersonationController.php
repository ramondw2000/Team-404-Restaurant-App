<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $target): RedirectResponse
    {
        $originalAdminId = session('impersonation.original_user_id');
        $actingAdmin = $originalAdminId ? User::find($originalAdminId) : Auth::user();

        abort_if(! $actingAdmin || ! $actingAdmin->roles()->where('is_administrator', true)->exists(), 403);
        abort_if($target->roles()->where('is_administrator', true)->exists(), 403);
        abort_if($target->id === $actingAdmin->id, 403);

        session([
            'impersonation.original_user_id' => $actingAdmin->id,
            'impersonation.user_id' => $target->id,
        ]);

        return redirect()->route('dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        abort_if(! session()->has('impersonation.original_user_id'), 403);

        session()->forget(['impersonation.original_user_id', 'impersonation.user_id']);

        return redirect()->route('accounts.index');
    }
}
