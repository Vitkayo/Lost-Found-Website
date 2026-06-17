<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmailCodeService;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, EmailCodeService $codes)
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $codes->verify($request->user(), EmailCodeService::VERIFY_EMAIL, $validated['code']);
        $request->user()->markEmailAsVerified();

        return response()->json(['message' => 'Email verified successfully']);
    }

    public function resend(Request $request, EmailCodeService $codes)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified']);
        }

        $codes->send($request->user(), EmailCodeService::VERIFY_EMAIL);

        return response()->json(['message' => 'A new verification code has been sent']);
    }
}
