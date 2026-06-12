@extends('layouts.admin')

@section('title', 'Appointment Calendar')
@section('page-title', 'Appointment Calendar')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ $month->format('F Y') }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.appointments.calendar', ['month' => $month->copy()->subMonth()->format('Y-m-d')]) }}" class="btn btn-outline-secondary">Previous</a>
            <a href="{{ route('staff.appointments.calendar', ['month' => now()->format('Y-m-d')]) }}" class="btn btn-outline-primary">Today</a>
            <a href="{{ route('staff.appointments.calendar', ['month' => $month->copy()->addMonth()->format('Y-m-d')]) }}" class="btn btn-outline-secondary">Next</a>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-7 g-2">
        @for($day = $start->copy()->startOfWeek(); $day <= $end->copy()->endOfWeek(); $day->addDay())
            @php $key = $day->format('Y-m-d'); @endphp
            <div class="col">
                <div class="card soft-card h-100 {{ $day->month !== $month->month ? 'opacity-50' : '' }}">
                    <div class="card-body p-2" style="min-height: 145px;">
                        <div class="fw-semibold small mb-2">{{ $day->format('D d') }}</div>
                        @foreach($appointmentsByDate->get($key, collect()) as $appointment)
                            <a href="{{ route('staff.appointments.show', $appointment) }}" class="d-block small text-decoration-none border rounded p-1 mb-1">
                                {{ $appointment->start_time?->format('H:i') }} {{ $appointment->person?->full_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endfor
    </div>
@endsection
