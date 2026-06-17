<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request, EmailCodeService $codes)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'student_id' => ['nullable', 'string', 'max:60'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $codes->send($user, EmailCodeService::VERIFY_EMAIL);

        return redirect()->route('verification.notice')->with('success', 'Account created. Check your email for a verification code.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The email or password is incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($request->user()->status === 'suspended') {
            Auth::logout();
            $request->session()->invalidate();

            return back()->withErrors(['email' => 'This account is suspended.'])->onlyInput('email');
        }

        if (! $request->user()->isAdmin() && ! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')->with('success', 'Welcome back. Please verify your email to continue.');
        }

        return redirect()->intended(route('home'))->with('success', 'Welcome back.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }
}
