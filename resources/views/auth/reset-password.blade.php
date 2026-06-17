@extends('layouts.main')

@section('title', 'Set New Password')

@section('content')
<div class="cf-page">
    <section class="cf-board-hero">
        <div class="cf-container">
            <h1>Set New Password</h1>
            <p>Use the 6-digit code from your email to choose a new password.</p>
        </div>
    </section>
    <section class="cf-container cf-form-shell">
        <form method="post" action="{{ route('password.update') }}" class="cf-page-form cf-auth-form">
            @csrf
            <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus>
                @error('email')<small class="cf-error">{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Reset Code</span>
                <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
                @error('code')<small class="cf-error">{{ $message }}</small>@enderror
            </label>
            <div class="cf-auth-note">
                <strong>Code validity:</strong> reset codes expire after 10 minutes.
                @if ($codeExpiresAt)
                    <div class="cf-auth-timer" data-live-countdown data-expires-at="{{ $codeExpiresAt->toIso8601String() }}" data-expired-text="This reset code has expired. Request a new one below.">
                        Time remaining: --:--
                    </div>
                @endif
            </div>
            <label>
                <span>New Password</span>
                <div class="cf-password-field">
                    <input type="password" name="password" required>
                    <button type="button" class="cf-password-toggle" data-password-toggle aria-label="Show new password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')<small class="cf-error">{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Confirm New Password</span>
                <div class="cf-password-field">
                    <input type="password" name="password_confirmation" required>
                    <button type="button" class="cf-password-toggle" data-password-toggle aria-label="Show password confirmation">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </label>
            <button class="cf-btn cf-btn-primary" type="submit">Update Password</button>
        </form>

        @if ($email)
            <form method="post" action="{{ route('password.email') }}" class="cf-page-form cf-auth-form mt-3" data-resend-form data-resend-key="password-reset:{{ $email }}" data-resend-seconds="60" @if($canResendAt) data-resend-until="{{ $canResendAt->toIso8601String() }}" @endif>
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                @error('email')<small class="cf-error">{{ $message }}</small>@enderror
                <div class="cf-auth-note" data-resend-message>
                    Need another reset code? Request a new email here.
                </div>
                <button class="cf-btn cf-btn-outline" type="submit" data-resend-button>Send New Code</button>
            </form>
        @endif
    </section>
</div>
@endsection
