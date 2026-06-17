<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    public function send(Request $request, EmailCodeService $codes)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $codes->sendPasswordReset($validated['email']);

        return response()->json(['message' => 'If that email exists, a reset code has been sent']);
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
            $user->forceFill(['password' => Hash::make($validated['password'])])->save();
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'If the code was valid, your password has been updated']);
    }
}
