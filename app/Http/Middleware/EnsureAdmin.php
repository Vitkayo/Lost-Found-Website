<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $isAdminUser = $user?->isAdmin() === true
            && $user?->status === 'active';

        if (! $isAdminUser) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'Please log in as admin first.');
        }

        return $next($request);
    }
}
