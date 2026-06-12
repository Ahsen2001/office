@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
    <h1 class="h3 mb-1">Staff Dashboard</h1>
    <p class="text-muted mb-4">Register people, scan QR/barcodes, and create service applications.</p>

    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="text-muted">Registered People</div><div class="fs-3 fw-semibold">{{ $peopleCount }}</div></div></div></div>
        <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="text-muted">Applications</div><div class="fs-3 fw-semibold">{{ $applicationsCount }}</div></div></div></div>
        <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="text-muted">Today's Appointments</div><div class="fs-3 fw-semibold">{{ $todayAppointments }}</div></div></div></div>
        <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="text-muted">Appointment Calendar</div><a href="{{ route('staff.appointments.calendar') }}" class="btn btn-sm btn-primary mt-2">Open Calendar</a></div></div></div>
    </div>

    <div class="card stat-card">
        <div class="card-body">
            <h2 class="h5 mb-3">Upcoming Appointments</h2>
            @forelse($upcomingAppointments as $appointment)
                <div class="border-bottom py-2">{{ $appointment->appointment_date?->format('Y-m-d') }} {{ $appointment->start_time?->format('H:i') }} - {{ $appointment->person?->full_name }} <span class="text-muted">{{ $appointment->department?->name }}</span></div>
            @empty
                <div class="text-muted">No upcoming appointments.</div>
            @endforelse
        </div>
    </div>
@endsection
