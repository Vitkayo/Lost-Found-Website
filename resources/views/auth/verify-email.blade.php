@extends('layouts.main')

@section('title', 'Verify Email')

@section('content')
<div class="cf-page">
    <section class="cf-board-hero">
        <div class="cf-container">
            <h1>Verify Your Email</h1>
            <p>Enter the 6-digit code we sent to {{ auth()->user()->email }}.</p>
        </div>
    </section>
    <section class="cf-container cf-form-shell">
        <form method="post" action="{{ route('verification.verify') }}" class="cf-page-form cf-auth-form">
            @csrf
            <label>
                <span>Verification Code</span>
                <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus>
                @error('code')<small class="cf-error">{{ $message }}</small>@enderror
            </label>
            <div class="cf-auth-note">
                <strong>Code validity:</strong> verification codes expire after 10 minutes.
                @if ($codeExpiresAt)
                    <div class="cf-auth-timer" data-live-countdown data-expires-at="{{ $codeExpiresAt->toIso8601String() }}" data-expired-text="This code has expired. Request a new verification code.">
                        Time remaining: --:--
                    </div>
                @endif
            </div>
            <button class="cf-btn cf-btn-primary" type="submit">Verify Email</button>
        </form>

        <form method="post" action="{{ route('verification.send') }}" class="cf-page-form cf-auth-form mt-3" data-resend-form data-resend-key="verification:{{ auth()->user()->email }}" data-resend-seconds="60" @if($canResendAt) data-resend-until="{{ $canResendAt->toIso8601String() }}" @endif>
            @csrf
            @error('email')<small class="cf-error">{{ $message }}</small>@enderror
            <div class="cf-auth-note" data-resend-message>
                You can request a new code if you did not receive the email.
            </div>
            <button class="cf-btn cf-btn-outline" type="submit" data-resend-button>Send New Code</button>
        </form>
    </section>
</div>
@endsection
