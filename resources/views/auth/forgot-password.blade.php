@extends('layouts.main')

@section('title', 'Forgot Password')

@section('content')
<div class="cf-page">
    <section class="cf-board-hero">
        <div class="cf-container">
            <h1>Reset Password</h1>
            <p>Enter your email and we will send a 6-digit reset code.</p>
        </div>
    </section>
    <section class="cf-container cf-form-shell">
        <form method="post" action="{{ route('password.email') }}" class="cf-page-form cf-auth-form">
            @csrf
            <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')<small class="cf-error">{{ $message }}</small>@enderror
            </label>
            <button class="cf-btn cf-btn-primary" type="submit">Send Reset Code</button>
            <p class="cf-auth-switch">Remember your password? <a href="{{ route('login') }}">Log in</a></p>
        </form>
    </section>
</div>
@endsection
