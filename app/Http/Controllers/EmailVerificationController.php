<?php

namespace App\Http\Controllers;

use App\Models\EmailCode;
use App\Services\EmailCodeService;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home')->with('success', 'Your email is already verified.');
        }

        $latestCode = EmailCode::query()
            ->where('user_id', $request->user()->id)
            ->where('email', $request->user()->email)
            ->where('purpose', EmailCodeService::VERIFY_EMAIL)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        return view('auth.verify-email', [
            'codeExpiresAt' => $latestCode?->expires_at,
            'canResendAt' => $latestCode?->sent_at?->copy()->addSeconds(60),
        ]);
    }

    public function verify(Request $request, EmailCodeService $codes)
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $codes->verify($request->user(), EmailCodeService::VERIFY_EMAIL, $validated['code']);
        $request->user()->markEmailAsVerified();

        return redirect()->intended(route('home'))->with('success', 'Email verified successfully.');
    }

    public function resend(Request $request, EmailCodeService $codes)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home')->with('success', 'Your email is already verified.');
        }

        $codes->send($request->user(), EmailCodeService::VERIFY_EMAIL);

        return back()->with('success', 'A new verification code has been sent.');
    }
}
