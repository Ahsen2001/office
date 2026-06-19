@extends('layouts.guest')

@section('title', 'Login - '.config('app.name'))

@section('content')
    <a href="{{ route('public.home') }}" class="small text-decoration-none d-inline-flex align-items-center mb-4"><i class="fa-solid fa-arrow-left me-2"></i> Back to Home</a>
    <h1 class="h3 fw-bold mb-1">Staff login</h1>
    <p class="text-muted mb-4">Sign in with your authorized office account.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                <button class="btn btn-outline-secondary" type="button" data-password-toggle aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input id="remember" class="form-check-input" type="checkbox" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            @if (Route::has('password.request'))
                <a class="small" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-right-to-bracket me-1"></i> Login</button>
    </form>

    <p class="text-muted small text-center mt-4 mb-0">Access is limited to authorized administrators, management officers, reception staff, branch heads, and branch staff.</p>

    <script>
        document.querySelector('[data-password-toggle]')?.addEventListener('click', (event) => {
            const password = document.getElementById('password');
            const icon = event.currentTarget.querySelector('i');
            const showing = password.type === 'text';
            password.type = showing ? 'password' : 'text';
            icon.className = showing ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
            event.currentTarget.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    </script>
@endsection
