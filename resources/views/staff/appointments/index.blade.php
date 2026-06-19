@extends('layouts.admin')

@section('title', 'Appointments')
@section('page-title', 'Appointment Management')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card soft-card"><div class="card-body"><div class="text-muted">Today's Appointments</div><div class="fs-3 fw-bold">{{ $todayAppointments }}</div></div></div></div>
        <div class="col-md-8">
            <div class="d-flex justify-content-end gap-2 h-100 align-items-center">
                <a href="{{ route('staff.appointments.calendar') }}" class="btn btn-outline-primary">Calendar View</a>
                @if($canCreate)<a href="{{ route('staff.appointments.create') }}" class="btn btn-primary">Book Appointment</a>@endif
            </div>
        </div>
    </div>

    <form method="GET" class="card soft-card mb-4"><div class="card-body row g-3 align-items-end">
        <div class="col-md-3"><label class="form-label">Date</label><input type="date" name="date" value="{{ $date }}" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Branch</label><select name="branch_id" class="form-select"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All statuses</option>@foreach(['scheduled'=>'Scheduled','completed'=>'Completed','cancelled'=>'Cancelled','missed'=>'Missed','rescheduled'=>'Rescheduled'] as $value=>$label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-3"><button class="btn btn-outline-primary w-100">Filter</button></div>
    </div></form>

    <div class="card soft-card"><div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>No</th><th>Person</th><th>Branch</th><th>Date/Time</th><th>Status</th><th>Officer</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr>
                        <td>{{ $appointment->appointment_no }}</td>
                        <td>{{ $appointment->person?->full_name }}</td>
                        <td>{{ $appointment->branch?->name }}</td>
                        <td>{{ $appointment->appointment_date?->format('Y-m-d') }} {{ $appointment->start_time?->format('H:i') }}</td>
                        <td><span class="badge text-bg-secondary">{{ ucfirst($appointment->status) }}</span></td>
                        <td>{{ $appointment->officer?->name ?? 'Unassigned' }}</td>
                        <td class="text-end"><a href="{{ route('staff.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">View</a>@if($canCreate)<a href="{{ route('staff.appointments.edit', $appointment) }}" class="btn btn-sm btn-outline-secondary">Edit</a>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No appointments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div><div class="card-body border-top">{{ $appointments->links() }}</div></div>
@endsection
