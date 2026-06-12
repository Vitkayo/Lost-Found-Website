@extends('layouts.main')

@section('title', 'Create Account')

@section('content')
<div class="cf-page">
    <section class="cf-board-hero">
        <div class="cf-container">
            <h1>Create Account</h1>
            <p>Join Campus Found to manage reports and verify ownership claims.</p>
        </div>
    </section>
    <section class="cf-container cf-form-shell">
        <form method="post" action="{{ route('register.store') }}" class="cf-page-form cf-auth-form">
            @csrf
            <label><span>Name</span><input type="text" name="name" value="{{ old('name') }}" required>@error('name')<small class="cf-error">{{ $message }}</small>@enderror</label>
            <label><span>Email</span><input type="email" name="email" value="{{ old('email') }}" required>@error('email')<small class="cf-error">{{ $message }}</small>@enderror</label>
            <div class="cf-form-grid">
                <label><span>Phone <small>optional</small></span><input type="text" name="phone" value="{{ old('phone') }}"></label>
                <label><span>Student ID <small>optional</small></span><input type="text" name="student_id" value="{{ old('student_id') }}"></label>
            </div>
            <label><span>Password</span><input type="password" name="password" required>@error('password')<small class="cf-error">{{ $message }}</small>@enderror</label>
            <label><span>Confirm Password</span><input type="password" name="password_confirmation" required></label>
            <button class="cf-btn cf-btn-primary" type="submit">Create Account</button>
            <p class="cf-auth-switch">Already registered? <a href="{{ route('login') }}">Log in</a></p>
        </form>
    </section>
</div>
@endsection
