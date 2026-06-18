@extends('layouts.public')

@section('title', 'Office Service Management System')
@section('meta-description', 'Access office services, understand the process, and track applications securely using QR or barcode technology.')

@section('content')
    <header class="public-hero">
        <div class="container">
            <div class="public-hero-content">
                <span class="public-eyebrow"><i class="fa-solid fa-shield-halved"></i> Secure public service access</span>
                <h1>Office Service Management System</h1>
                <p class="lead mb-4">Apply for office services with staff support, receive a unique person ID, and track every application through a secure QR or barcode-enabled workflow.</p>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a href="{{ route('public.status') }}" class="btn btn-public-success public-btn"><i class="fa-solid fa-magnifying-glass me-1"></i> Track Application</a>
                    <a href="{{ route('login') }}" class="btn btn-light public-btn"><i class="fa-solid fa-right-to-bracket me-1"></i> Login</a>
                    <a href="{{ route('public.contact') }}" class="btn btn-outline-light public-btn"><i class="fa-regular fa-envelope me-1"></i> Contact Office</a>
                </div>
                <div class="hero-trust-row">
                    <span><i class="fa-solid fa-qrcode me-1"></i> QR and barcode tracking</span>
                    <span><i class="fa-solid fa-lock me-1"></i> Privacy-conscious status checks</span>
                    <span><i class="fa-solid fa-building me-1"></i> Department-based processing</span>
                </div>
            </div>
        </div>
    </header>

    <section class="public-section">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <div class="section-heading mb-lg-0">
                        <span class="public-eyebrow text-primary"><i class="fa-solid fa-circle-info"></i> One connected office</span>
                        <h2>Public services without the uncertainty</h2>
                        <p>The system connects registration, service applications, departments, documents, appointments, and status updates. People receive clear reference numbers while authorized staff work from one secure record.</p>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="public-card p-4 text-center">
                                <div class="fs-2 fw-bold text-primary">{{ number_format($serviceCount) }}+</div>
                                <div class="text-muted small">Active services</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="public-card p-4 text-center">
                                <div class="fs-2 fw-bold text-success">{{ number_format($departmentCount) }}+</div>
                                <div class="text-muted small">Departments</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="public-section public-section-soft">
        <div class="container">
            <div class="section-heading text-center">
                <span class="public-eyebrow text-primary"><i class="fa-solid fa-briefcase"></i> Service categories</span>
                <h2>Common office services</h2>
                <p>Find the right department, understand the requirements, and arrive prepared.</p>
            </div>
            @php
                $categories = [
                    ['Certificate Requests', 'Official certificates and certified records.', 'fa-certificate'],
                    ['Document Verification', 'Verification of submitted records and identity documents.', 'fa-file-circle-check'],
                    ['License Requests', 'License applications, renewals, and related approvals.', 'fa-id-card'],
                    ['Approvals', 'Department and management approval workflows.', 'fa-stamp'],
                    ['Complaints', 'Formal complaint registration and follow-up.', 'fa-comments'],
                    ['Appointments', 'Scheduled visits with the correct department or officer.', 'fa-calendar-check'],
                ];
            @endphp
            <div class="row g-3">
                @foreach($categories as [$name, $description, $icon])
                    <div class="col-md-6 col-xl-4">
                        <div class="card public-card">
                            <div class="card-body">
                                <span class="public-icon mb-3"><i class="fa-solid {{ $icon }}"></i></span>
                                <h3 class="h5">{{ $name }}</h3>
                                <p class="text-muted mb-0">{{ $description }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('public.services') }}" class="btn btn-public-primary public-btn">Browse All Services <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="tracking-panel d-flex align-items-center p-4 p-lg-5">
                        <div class="w-100 text-center">
                            <div class="tracking-code mb-4">
                                <i class="fa-solid fa-qrcode" style="font-size: 7rem;"></i>
                            </div>
                            <div class="d-flex justify-content-center gap-1 mx-auto" style="max-width: 250px;">
                                @foreach([18, 9, 28, 14, 7, 22, 11, 30] as $width)
                                    <span class="tracking-line" style="width: {{ $width }}px;"></span>
                                @endforeach
                            </div>
                            <div class="small text-white-50 mt-2">PER-2026-000001</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="section-heading">
                        <span class="public-eyebrow text-success"><i class="fa-solid fa-qrcode"></i> Digital tracking</span>
                        <h2>Your unique code connects every visit</h2>
                        <p>When staff register a person, the system creates a unique person ID with QR and barcode values. Authorized staff can scan the code to retrieve the correct profile quickly, reducing duplicate records and manual searching.</p>
                    </div>
                    <div class="d-grid gap-3">
                        <div class="d-flex gap-3"><span class="public-icon green"><i class="fa-solid fa-bolt"></i></span><div><h3 class="h6 mb-1">Faster service counters</h3><p class="text-muted mb-0">Staff retrieve the correct profile and application history in seconds.</p></div></div>
                        <div class="d-flex gap-3"><span class="public-icon green"><i class="fa-solid fa-fingerprint"></i></span><div><h3 class="h6 mb-1">Reliable identification</h3><p class="text-muted mb-0">Unique codes reduce duplicate registrations and record confusion.</p></div></div>
                        <div class="d-flex gap-3"><span class="public-icon green"><i class="fa-solid fa-mobile-screen"></i></span><div><h3 class="h6 mb-1">Mobile-friendly tracking</h3><p class="text-muted mb-0">QR codes can be scanned from printed cards or compatible mobile screens.</p></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="public-section public-section-blue">
        <div class="container">
            <div class="section-heading text-center">
                <span class="public-eyebrow text-primary"><i class="fa-solid fa-list-check"></i> How it works</span>
                <h2>From registration to completion</h2>
                <p>A clear workflow keeps people informed and gives each department accountable work queues.</p>
            </div>
            <div class="row g-4">
                @foreach([
                    ['Register', 'Office staff records the person and issues a unique QR/barcode card.'],
                    ['Apply', 'Staff creates the selected service application and confirms its requirements.'],
                    ['Process', 'The application moves to the responsible department and assigned officer.'],
                    ['Track', 'Use the application number, person ID, NIC/passport, or QR scan to check progress.'],
                ] as $index => [$title, $text])
                    <div class="col-md-6 col-xl-3">
                        <div class="d-flex gap-3">
                            <span class="step-number">{{ $index + 1 }}</span>
                            <div><h3 class="h5">{{ $title }}</h3><p class="text-muted mb-0">{{ $text }}</p></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="container">
            <div class="section-heading text-center">
                <span class="public-eyebrow text-success"><i class="fa-solid fa-thumbs-up"></i> Benefits</span>
                <h2>Better for people and office teams</h2>
            </div>
            <div class="row g-3">
                @foreach([
                    ['Clear progress', 'See the current status and latest update without exposing private records.', 'fa-chart-line'],
                    ['Fewer repeat visits', 'Know when documents or appointments are needed before returning.', 'fa-route'],
                    ['Accountable processing', 'Every status change is recorded with department and staff context.', 'fa-clipboard-check'],
                    ['Organized records', 'Applications, documents, payments, and appointments remain connected.', 'fa-folder-tree'],
                ] as [$title, $text, $icon])
                    <div class="col-md-6 col-xl-3">
                        <div class="card public-card">
                            <div class="card-body">
                                <span class="public-icon green mb-3"><i class="fa-solid {{ $icon }}"></i></span>
                                <h3 class="h5">{{ $title }}</h3>
                                <p class="text-muted mb-0">{{ $text }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-cta">
        <div class="container text-center">
            <h2 class="display-6 fw-bold">Ready to check your application?</h2>
            <p class="text-white-50 mx-auto mb-4" style="max-width: 650px;">Use your application number, person ID, NIC/passport number, or QR code to see the latest public status.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ route('public.status') }}" class="btn btn-light public-btn"><i class="fa-solid fa-magnifying-glass me-1"></i> Track Application</a>
                <a href="{{ route('public.contact') }}" class="btn btn-outline-light public-btn">Contact Office</a>
            </div>
        </div>
    </section>
@endsection
