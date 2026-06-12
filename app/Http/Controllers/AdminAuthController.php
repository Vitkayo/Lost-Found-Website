<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function create()
    {
        if (session('is_admin') === true) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if ($request->password !== config('lostfound.admin_password')) {
            return back()->withErrors(['password' => 'Access Denied: Invalid Admin Key']);
        }

        $request->session()->put('is_admin', true);

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request)
    {
        $request->session()->forget('is_admin');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'You have been logged out.');
    }
}
