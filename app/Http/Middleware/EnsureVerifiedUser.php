<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isAdmin() && ! $user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Email verification is required.'], 403);
            }

            return redirect()
                ->route('verification.notice')
                ->with('error', 'Please verify your email before continuing.');
        }

        return $next($request);
    }
}
