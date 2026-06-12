@extends('layouts.main')

@section('title', 'Login')

@section('content')
<div class="cf-page">
    <section class="cf-board-hero">
        <div class="cf-container">
            <h1>Welcome Back</h1>
            <p>Log in to report items, submit verified claims, and manage your reports.</p>
        </div>
    </section>
    <section class="cf-container cf-form-shell">
        <form method="post" action="{{ route('login.store') }}" class="cf-page-form cf-auth-form">
            @csrf
            <label><span>Email</span><input type="email" name="email" value="{{ old('email') }}" required autofocus>@error('email')<small class="cf-error">{{ $message }}</small>@enderror</label>
            <label><span>Password</span><input type="password" name="password" required>@error('password')<small class="cf-error">{{ $message }}</small>@enderror</label>
            <label class="cf-check-row"><input type="checkbox" name="remember" value="1"><span>Remember me</span></label>
            <button class="cf-btn cf-btn-primary" type="submit">Log In</button>
            <p class="cf-auth-switch">No account yet? <a href="{{ route('register') }}">Create one</a></p>
        </form>
    </section>
</div>
@endsection
