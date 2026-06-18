<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta-description', 'Access public office services, track applications, and contact the office securely.')">
    <title>@yield('title', config('app.name', 'Office Service'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/public-site.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="public-body">
    <nav class="navbar navbar-expand-lg public-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('public.home') }}">
                <span class="public-brand-mark"><i class="fa-solid fa-building-columns"></i></span>
                <span>
                    <span class="public-brand-name d-block">{{ config('app.name', 'Office Service') }}</span>
                    <span class="public-brand-tagline d-block">Public service and application tracking</span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavigation" aria-controls="publicNavigation" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="publicNavigation">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.home') ? 'active' : '' }}" href="{{ route('public.home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.about') ? 'active' : '' }}" href="{{ route('public.about') }}">About</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.services') ? 'active' : '' }}" href="{{ route('public.services') }}">Services</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.status') ? 'active' : '' }}" href="{{ route('public.status') }}">Track Application</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.contact') ? 'active' : '' }}" href="{{ route('public.contact') }}">Contact</a></li>
                </ul>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-public-primary public-btn ms-lg-3"><i class="fa-solid fa-gauge-high me-1"></i> Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-public-primary public-btn ms-lg-3"><i class="fa-solid fa-right-to-bracket me-1"></i> Staff Login</a>
                @endauth
            </div>
        </div>
    </nav>

    @if(session('success') || $errors->any())
        <div class="container pt-3">
            @include('partials.flash')
        </div>
    @endif
    @yield('content')

    <footer class="public-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="public-brand-mark"><i class="fa-solid fa-building-columns"></i></span>
                        <h2 class="h5 mb-0">{{ config('app.name', 'Office Service') }}</h2>
                    </div>
                    <p class="mb-0">A secure public-service platform for registration, application processing, QR and barcode tracking, appointments, and status updates.</p>
                </div>
                <div class="col-6 col-lg-2">
                    <h3 class="h6 mb-3">Quick Links</h3>
                    <div class="footer-link-list">
                        <a href="{{ route('public.home') }}">Home</a>
                        <a href="{{ route('public.about') }}">About</a>
                        <a href="{{ route('public.services') }}">Services</a>
                        <a href="{{ route('public.contact') }}">Contact</a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h3 class="h6 mb-3">Online Access</h3>
                    <div class="footer-link-list">
                        <a href="{{ route('public.status') }}">Track Application</a>
                        <a href="{{ route('login') }}">Staff Login</a>
                        <a href="{{ route('public.services') }}">Service Guide</a>
                    </div>
                </div>
                <div class="col-lg-3">
                    <h3 class="h6 mb-3">Office Hours</h3>
                    <p class="mb-2"><i class="fa-regular fa-clock me-2"></i>Monday-Friday, 8:30 AM-4:30 PM</p>
                    <p class="mb-0"><i class="fa-solid fa-phone me-2"></i>+94 11 000 0000</p>
                </div>
            </div>
            <div class="public-footer-bottom d-flex flex-wrap justify-content-between gap-2 small">
                <span>&copy; {{ now()->year }} {{ config('app.name', 'Office Service') }}. All rights reserved.</span>
                <span>Secure public service management with QR and barcode tracking.</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
