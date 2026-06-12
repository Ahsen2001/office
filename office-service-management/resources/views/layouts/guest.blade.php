<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Office Service'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #f7f9fc, #e9eef6); }
        .auth-card { max-width: 460px; width: 100%; border: 0; box-shadow: 0 1rem 2rem rgba(15, 23, 42, .08); }
    </style>
</head>
<body>
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <div class="auth-card card">
            <div class="card-body p-4 p-md-5">
                <div class="mb-4 text-center">
                    <a href="{{ url('/') }}" class="text-decoration-none">
                        <div class="fw-bold fs-4 text-dark">{{ config('app.name', 'Office Service') }}</div>
                        <div class="text-muted small">Secure office access</div>
                    </a>
                </div>
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
