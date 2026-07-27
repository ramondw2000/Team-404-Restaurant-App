<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HandleImpersonation
{
    private const string SESSION_ORIGINAL_KEY = 'impersonation.original_user_id';

    private const string SESSION_TARGET_KEY = 'impersonation.user_id';

    public function handle(Request $request, Closure $next): Response
    {
        $originalUserId = session(self::SESSION_ORIGINAL_KEY);

        if (! $originalUserId) {
            return $next($request);
        }

        if ($request->routeIs('impersonation.stop')) {
            return $next($request);
        }

        $originalAdmin = User::find($originalUserId);

        if (! $this->isValidAdmin($originalAdmin)) {
            $this->clearSession();

            return $next($request);
        }

        $impersonatedUser = User::find(session(self::SESSION_TARGET_KEY));

        if (! $this->isValidImpersonationTarget($impersonatedUser)) {
            $this->clearSession();

            return $next($request);
        }

        Auth::setUser($impersonatedUser);
        DB::beginTransaction();
        $request->attributes->set('impersonation_active', true);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($request->attributes->get('impersonation_active')) {
            if ($request->attributes->get('impersonation_stopped')) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        }
    }

    private function isValidAdmin(?User $user): bool
    {
        return $user !== null && $user->roles()->where('is_administrator', true)->exists();
    }

    private function isValidImpersonationTarget(?User $user): bool
    {
        return $user !== null && ! $user->roles()->where('is_administrator', true)->exists();
    }

    private function clearSession(): void
    {
        session()->forget([self::SESSION_ORIGINAL_KEY, self::SESSION_TARGET_KEY]);
    }
}
