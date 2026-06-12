@extends('layouts.admin')
@section('title', $application->application_no)
@section('page-title', 'Application Details')
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
    <div><h1 class="h3 mb-1">{{ $application->application_no }}</h1><div class="text-muted">{{ $application->person?->full_name }} - {{ $application->service?->name }}</div></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('staff.applications.edit', $application) }}">Edit</a><a class="btn btn-success" href="{{ route('staff.applications.receipt', $application) }}">Print Receipt</a></div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card soft-card mb-4"><div class="card-body">
            <h2 class="h5 mb-3">Application Information</h2>
            <div class="row g-3">
                <div class="col-md-6"><div class="text-muted small">Person</div><a href="{{ route('staff.people.show', $application->person) }}">{{ $application->person?->full_name }}</a></div>
                <div class="col-md-6"><div class="text-muted small">Status</div><span class="badge text-bg-secondary">{{ $application->status?->name }}</span></div>
                <div class="col-md-6"><div class="text-muted small">Department</div>{{ $application->department?->name }}</div>
                <div class="col-md-6"><div class="text-muted small">Assigned officer</div>{{ $application->assignedOfficer?->name ?? 'Unassigned' }}</div>
                <div class="col-md-6"><div class="text-muted small">Deadline</div>{{ $application->due_date?->format('Y-m-d') }}</div>
                <div class="col-md-6"><div class="text-muted small">Priority</div>{{ ucfirst($application->priority) }}</div>
                <div class="col-12"><div class="text-muted small">Description</div>{{ $application->description }}</div>
                <div class="col-12"><div class="text-muted small">Remarks</div>{{ $application->remarks }}</div>
            </div>
        </div></div>
        <div class="card soft-card"><div class="card-body">
            <h2 class="h5 mb-3">Timeline</h2>
            <div class="timeline">
                @forelse($application->statusHistories as $history)
                    <div class="timeline-item"><div class="fw-semibold">{{ $history->toStatus?->name }}</div><div class="text-muted small">{{ $history->changed_at?->format('Y-m-d H:i') }} by {{ $history->changedBy?->name }}</div><div>{{ $history->remarks }}</div></div>
                @empty <div class="text-muted">No timeline entries.</div> @endforelse
            </div>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card soft-card mb-4"><div class="card-body">
            <h2 class="h5 mb-3">Update Status</h2>
            <form method="POST" action="{{ route('staff.applications.status', $application) }}">
                @csrf @method('PATCH')
                <select name="status_id" class="form-select mb-2">@foreach($statuses as $status)<option value="{{ $status->id }}" @selected($application->status_id == $status->id)>{{ $status->name }}</option>@endforeach</select>
                <textarea name="remarks" class="form-control mb-2" rows="3" placeholder="Remarks"></textarea>
                <textarea name="rejection_reason" class="form-control mb-2" rows="2" placeholder="Rejection reason if rejected"></textarea>
                <button class="btn btn-primary w-100">Update</button>
            </form>
        </div></div>
        <div class="card soft-card"><div class="card-body">
            <h2 class="h5 mb-3">Required Documents</h2>
            @forelse($application->required_documents ?? [] as $document)<div class="border-bottom py-2">{{ $document }}</div>@empty<div class="text-muted">No required documents configured.</div>@endforelse
        </div></div>
    </div>
</div>
@endsection
