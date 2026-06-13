<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Office Service'))</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('css/office.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-shell">
        @include('admin.partials.sidebar')

        <div class="admin-main">
            @include('admin.partials.navbar')
            <main class="container-fluid p-3 p-lg-4">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            button.addEventListener('click', () => document.querySelector('.admin-sidebar')?.classList.toggle('show'));
        });
    </script>
    @stack('scripts')
</body>
</html>
