@extends('layouts.admin')

@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Service intake workspace</h1>
            <p class="text-muted mb-0">Register people, scan QR/barcodes, and create service applications.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('staff.people.create') }}" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Register Person</a>
            <a href="{{ route('staff.scanner.index') }}" class="btn btn-success"><i class="fa-solid fa-qrcode me-1"></i> Scan Code</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card soft-card metric-card h-100">
                <div class="card-body">
                    <div class="metric-icon mb-3"><i class="fa-solid fa-address-card"></i></div>
                    <div class="text-muted">Registered People</div>
                    <div class="fs-3 fw-bold">{{ number_format($peopleCount) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card soft-card metric-card h-100">
                <div class="card-body">
                    <div class="metric-icon mb-3"><i class="fa-solid fa-folder-open"></i></div>
                    <div class="text-muted">Applications</div>
                    <div class="fs-3 fw-bold">{{ number_format($applicationsCount) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card soft-card metric-card h-100">
                <div class="card-body">
                    <div class="metric-icon success mb-3"><i class="fa-solid fa-calendar-day"></i></div>
                    <div class="text-muted">Today's Appointments</div>
                    <div class="fs-3 fw-bold">{{ number_format($todayAppointments) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card soft-card metric-card h-100">
                <div class="card-body">
                    <div class="metric-icon success mb-3"><i class="fa-solid fa-calendar-days"></i></div>
                    <div class="text-muted">Calendar</div>
                    <a href="{{ route('staff.appointments.calendar') }}" class="btn btn-sm btn-outline-primary mt-2">Open Calendar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card soft-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Upcoming Appointments</h2>
                <a href="{{ route('staff.appointments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            @forelse($upcomingAppointments as $appointment)
                <div class="d-flex flex-wrap justify-content-between gap-2 border-bottom py-3">
                    <div>
                        <div class="fw-semibold">{{ $appointment->person?->full_name }}</div>
                        <div class="text-muted small">{{ $appointment->department?->name }}</div>
                    </div>
                    <span class="badge status-scheduled align-self-start">{{ $appointment->appointment_date?->format('Y-m-d') }} {{ $appointment->start_time?->format('H:i') }}</span>
                </div>
            @empty
                <div class="text-muted">No upcoming appointments.</div>
            @endforelse
        </div>
    </div>
@endsection
