@extends('layouts.main')

@section('title', 'Admin Login')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center pb-5"
     style="background: linear-gradient(135deg, #174a7e 0%, #142033 72%, #0f1726 100%);">
    <div class="card shadow-lg p-4 border-2 border-dark" style="max-width: 420px; width: 90%; border-radius: 20px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">Admin Login</h3>
            <p class="text-muted small mb-0">Use an administrator account to open the dashboard.</p>
            @error('email')
                <div class="alert alert-danger mt-3 mb-0 py-2 small fw-bold">{{ $message }}</div>
            @enderror
            @error('password')
                <div class="alert alert-danger mt-3 mb-0 py-2 small fw-bold">{{ $message }}</div>
            @enderror
            @if (session('error'))
                <div class="alert alert-danger mt-3 mb-0 py-2 small fw-bold" data-auto-dismiss>{{ session('error') }}</div>
            @endif
        </div>
        <form method="post" action="{{ route('admin.login.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-bold text-uppercase text-secondary">Admin Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-control form-control-lg bg-light border-2 border-dark"
                       style="border-radius: 10px;" placeholder="admin@example.com" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-uppercase text-secondary">Password</label>
                <input type="password" name="password"
                       class="form-control form-control-lg bg-light border-2 border-dark"
                       style="border-radius: 10px;" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold shadow border-2 border-dark"
                    style="border-radius: 10px; background-color: #174a7e;">
                LOGIN TO DASHBOARD
            </button>
        </form>
        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="text-decoration-none text-muted small">Back to Homepage</a>
        </div>
    </div>
</div>
@endsection
