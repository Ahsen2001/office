@extends('layouts.admin')

@section('title', $appointment->appointment_no)
@section('page-title', 'Appointment Details')

@section('content')
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-4">
        <div><h1 class="h3 mb-1">{{ $appointment->appointment_no }}</h1><div class="text-muted">{{ $appointment->person?->full_name }} | {{ $appointment->department?->name }}</div></div>
        <a href="{{ route('staff.appointments.edit', $appointment) }}" class="btn btn-outline-primary">Edit Appointment</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card soft-card"><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="text-muted small">Application</div>{{ $appointment->application?->application_no ?? 'Person profile' }}</div>
                    <div class="col-md-6"><div class="text-muted small">Service</div>{{ $appointment->application?->service?->name ?? '-' }}</div>
                    <div class="col-md-6"><div class="text-muted small">Appointment Date</div>{{ $appointment->appointment_date?->format('Y-m-d') }}</div>
                    <div class="col-md-6"><div class="text-muted small">Appointment Time</div>{{ $appointment->start_time?->format('H:i') }}{{ $appointment->end_time ? ' - '.$appointment->end_time->format('H:i') : '' }}</div>
                    <div class="col-md-6"><div class="text-muted small">Status</div><span class="badge text-bg-secondary">{{ ucfirst($appointment->status) }}</span></div>
                    <div class="col-md-6"><div class="text-muted small">Assigned Officer</div>{{ $appointment->officer?->name ?? 'Unassigned' }}</div>
                    <div class="col-12"><div class="text-muted small">Purpose</div>{{ $appointment->purpose ?: '-' }}</div>
                    <div class="col-12"><div class="text-muted small">Remarks</div>{{ $appointment->remarks ?: '-' }}</div>
                </div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card soft-card mb-4"><div class="card-body">
                <h2 class="h5 mb-3">Update Status</h2>
                <form method="POST" action="{{ route('staff.appointments.status', $appointment) }}">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select mb-2">@foreach(['scheduled'=>'Scheduled','completed'=>'Completed','cancelled'=>'Cancelled','missed'=>'Missed','rescheduled'=>'Rescheduled'] as $value=>$label)<option value="{{ $value }}" @selected($appointment->status === $value)>{{ $label }}</option>@endforeach</select>
                    <textarea name="remarks" class="form-control mb-2" rows="2" placeholder="Remarks"></textarea>
                    <button class="btn btn-primary w-100">Update</button>
                </form>
            </div></div>
            <div class="card soft-card"><div class="card-body">
                <h2 class="h5 mb-3">Reschedule</h2>
                <form method="POST" action="{{ route('staff.appointments.reschedule', $appointment) }}">
                    @csrf @method('PATCH')
                    <input type="date" name="appointment_date" class="form-control mb-2" required>
                    <input type="time" name="start_time" class="form-control mb-2" required>
                    <input type="time" name="end_time" class="form-control mb-2">
                    <input type="text" name="remarks" class="form-control mb-2" placeholder="Reason">
                    <button class="btn btn-outline-primary w-100">Reschedule Appointment</button>
                </form>
                <form method="POST" action="{{ route('staff.appointments.cancel', $appointment) }}" class="mt-2">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-danger w-100">Cancel Appointment</button>
                </form>
            </div></div>
        </div>
    </div>
@endsection
