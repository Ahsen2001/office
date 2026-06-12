<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Office Service'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f8fb; }
        .page-shell { max-width: 1180px; }
        .stat-card { border: 0; box-shadow: 0 0.25rem 1rem rgba(15, 23, 42, .06); }
    </style>
</head>
<body>
    @include('layouts.navigation')

    <main class="container page-shell py-4">
        @include('partials.flash')

        @isset($header)
            <div class="mb-4">{{ $header }}</div>
        @endisset

        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
