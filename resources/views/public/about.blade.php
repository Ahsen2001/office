@extends('layouts.public')

@section('title', 'About Us - '.config('app.name'))

@section('content')
    <header class="public-page-header">
        <div class="container">
            <span class="public-eyebrow"><i class="fa-solid fa-building-columns"></i> About the office</span>
            <h1 class="mt-2 mb-3">Public service built around clarity</h1>
            <p>{{ $office['name'] }} supports people through organized registration, department processing, appointments, documents, and secure application tracking.</p>
        </div>
    </header>

    <section class="public-section">
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-6">
                    <div class="section-heading">
                        <span class="public-eyebrow text-primary"><i class="fa-solid fa-landmark"></i> Our office</span>
                        <h2>Accessible, accountable public services</h2>
                        <p>Our office receives requests from people, identifies the responsible department, and follows each application through review, processing, approval, completion, or another clearly recorded outcome.</p>
                    </div>
                    <p class="text-muted">The management system reduces fragmented paper-based work by connecting the person, service, department, assigned officer, documents, appointments, and status history in one authorized workflow.</p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="card public-card"><div class="card-body"><span class="public-icon mb-3"><i class="fa-solid fa-bullseye"></i></span><h3 class="h5">Mission</h3><p class="text-muted mb-0">Deliver timely, transparent, and respectful office services through organized digital processes.</p></div></div></div>
                        <div class="col-md-6"><div class="card public-card"><div class="card-body"><span class="public-icon green mb-3"><i class="fa-solid fa-eye"></i></span><h3 class="h5">Vision</h3><p class="text-muted mb-0">A trusted public office where people can access services and understand progress with confidence.</p></div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="public-section public-section-soft">
        <div class="container">
            <div class="section-heading text-center">
                <span class="public-eyebrow text-success"><i class="fa-solid fa-list-check"></i> Objectives</span>
                <h2>What the system is designed to achieve</h2>
            </div>
            <div class="row g-3">
                @foreach([
                    ['Centralize records', 'Maintain a reliable person profile connected to every office interaction.', 'fa-database'],
                    ['Improve turnaround', 'Route applications to the right department and responsible officer.', 'fa-stopwatch'],
                    ['Increase transparency', 'Record each application status change and its timing.', 'fa-magnifying-glass-chart'],
                    ['Protect information', 'Limit access by role and expose only safe status details publicly.', 'fa-user-shield'],
                    ['Support decisions', 'Give managers accurate reports on workload, outcomes, and service demand.', 'fa-chart-column'],
                    ['Reduce paper handling', 'Digitize documents, receipts, appointments, and internal remarks.', 'fa-file-circle-check'],
                ] as [$title, $text, $icon])
                    <div class="col-md-6 col-xl-4">
                        <div class="card public-card"><div class="card-body"><span class="public-icon mb-3"><i class="fa-solid {{ $icon }}"></i></span><h3 class="h5">{{ $title }}</h3><p class="text-muted mb-0">{{ $text }}</p></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <div class="tracking-panel d-flex align-items-center justify-content-center p-5">
                        <div class="text-center">
                            <div class="tracking-code mb-4"><i class="fa-solid fa-qrcode" style="font-size: 7rem;"></i></div>
                            <div class="fw-semibold">One code. One verified profile.</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="section-heading">
                        <span class="public-eyebrow text-primary"><i class="fa-solid fa-qrcode"></i> Why QR and barcode tracking</span>
                        <h2>Designed for speed without losing control</h2>
                        <p>QR and barcode tracking gives office staff a fast way to locate an existing person record while preserving the system's role-based controls.</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><h3 class="h6"><i class="fa-solid fa-check text-success me-2"></i>Fewer duplicate records</h3><p class="text-muted">Unique identifiers help staff confirm the correct profile before creating new applications.</p></div>
                        <div class="col-md-6"><h3 class="h6"><i class="fa-solid fa-check text-success me-2"></i>Shorter service time</h3><p class="text-muted">Scanning avoids repeated manual searches by name, phone, or identification number.</p></div>
                        <div class="col-md-6"><h3 class="h6"><i class="fa-solid fa-check text-success me-2"></i>Connected history</h3><p class="text-muted">Staff can see authorized application, document, note, and appointment history.</p></div>
                        <div class="col-md-6"><h3 class="h6"><i class="fa-solid fa-check text-success me-2"></i>Public convenience</h3><p class="text-muted">People can use QR values for limited status checks without seeing private profile data.</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
