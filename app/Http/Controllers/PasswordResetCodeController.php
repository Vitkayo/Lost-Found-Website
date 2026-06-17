<?php

namespace App\Http\Controllers;

use App\Models\EmailCode;
use App\Models\User;
use App\Services\EmailCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordResetCodeController extends Controller
{
    public function requestForm()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request, EmailCodeService $codes)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $codes->sendPasswordReset($validated['email']);

        return redirect()
            ->route('password.reset.form', ['email' => $validated['email']])
            ->with('success', 'If that email exists, a reset code has been sent.');
    }

    public function resetForm(Request $request)
    {
        $email = $request->query('email', '');
        $user = User::where('email', $email)->first();
        $latestCode = $user
            ? EmailCode::query()
                ->where('user_id', $user->id)
                ->where('email', $user->email)
                ->where('purpose', EmailCodeService::PASSWORD_RESET)
                ->whereNull('consumed_at')
                ->latest()
                ->first()
            : null;

        return view('auth.reset-password', [
            'email' => $email,
            'codeExpiresAt' => $latestCode?->expires_at,
            'canResendAt' => $latestCode?->sent_at?->copy()->addSeconds(60),
        ]);
    }

    public function reset(Request $request, EmailCodeService $codes)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $codes->verify($user, EmailCodeService::PASSWORD_RESET, $validated['code']);
            $user->forceFill([
                'password' => Hash::make($validated['password']),
            ])->save();
            $user->tokens()->delete();
        }

        return redirect()->route('login')->with('success', 'If the code was valid, your password has been updated.');
    }
}
