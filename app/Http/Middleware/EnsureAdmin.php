<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('is_admin') !== true) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'Please log in as admin first.');
        }

        return $next($request);
    }
}
