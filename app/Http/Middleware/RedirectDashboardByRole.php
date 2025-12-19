<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectDashboardByRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->path() === 'dashboard') {
            $user = $request->user();

            if ($user && $user->role instanceof \App\Enums\UserRole) {
                return redirect($user->role->redirectPath());
            }
        }

        return $next($request);
    }
}
