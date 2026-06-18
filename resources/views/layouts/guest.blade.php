<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Office Service'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --office-blue: #1d4ed8;
            --office-green: #059669;
            --office-line: #dbe4ef;
            --office-ink: #0f172a;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(29, 78, 216, .14), transparent 340px),
                radial-gradient(circle at bottom right, rgba(5, 150, 105, .12), transparent 360px),
                #f8fafc;
            color: var(--office-ink);
        }

        .auth-shell {
            min-height: 100vh;
        }

        .auth-panel {
            max-width: 1080px;
            width: 100%;
            background: #fff;
            border: 1px solid var(--office-line);
            border-radius: 26px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .12);
            overflow: hidden;
        }

        .auth-visual {
            background-image: url("{{ asset('images/public-office-hero.jpg') }}");
            background-position: center;
            background-size: cover;
            color: #fff;
            min-height: 560px;
            position: relative;
        }

        .auth-visual::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(10, 42, 82, .8);
        }

        .auth-visual > * {
            position: relative;
            z-index: 1;
        }

        .auth-mark {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .26);
        }

        .auth-card {
            width: 100%;
            max-width: 460px;
            border: 0;
        }

        .form-control {
            border-radius: 12px;
            border-color: var(--office-line);
            padding: .72rem .9rem;
        }

        .form-control:focus {
            border-color: var(--office-blue);
            box-shadow: 0 0 0 .2rem rgba(29, 78, 216, .12);
        }

        .btn {
            border-radius: 12px;
            font-weight: 700;
            padding: .72rem 1rem;
        }

        .btn-primary {
            background: var(--office-blue);
            border-color: var(--office-blue);
        }

        @media (max-width: 991.98px) {
            .auth-visual {
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell d-flex align-items-center justify-content-center p-3 p-lg-4">
        <div class="auth-panel row g-0">
            <section class="auth-visual col-lg-6 d-flex flex-column justify-content-between p-4 p-lg-5">
                <div>
                    <a href="{{ route('public.home') }}" class="auth-mark mb-4 text-white text-decoration-none" aria-label="Back to home"><i class="fa-solid fa-building-columns fs-3"></i></a>
                    <h1 class="display-6 fw-bold mb-3">Office Service Management System</h1>
                    <p class="lead opacity-75 mb-0">Secure service intake, QR tracking, department processing, payments, appointments, and reports in one office workspace.</p>
                </div>
                <div class="row g-3 mt-5">
                    <div class="col-6">
                        <div class="p-3 rounded-4" style="background: rgba(255,255,255,.13);">
                            <i class="fa-solid fa-shield-halved mb-2"></i>
                            <div class="fw-semibold">Role protected</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-4" style="background: rgba(255,255,255,.13);">
                            <i class="fa-solid fa-chart-line mb-2"></i>
                            <div class="fw-semibold">Live reporting</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
                <div class="auth-card">
                    <div class="mb-4">
                        <div class="text-muted small fw-semibold text-uppercase">Secure access</div>
                        <div class="fw-bold fs-4">Office portal</div>
                    </div>
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
            </section>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
