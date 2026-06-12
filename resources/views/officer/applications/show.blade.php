@extends('layouts.admin')

@section('title', $application->application_no)
@section('page-title', 'Review Application')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card soft-card mb-4"><div class="card-body">
                <h1 class="h4">{{ $application->application_no }}</h1>
                <div class="text-muted mb-3">{{ $application->service?->name }} | {{ $application->status?->name }}</div>
                <h2 class="h6">Person Details</h2>
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><strong>Name:</strong> {{ $application->person?->full_name }}</div>
                    <div class="col-md-6"><strong>ID:</strong> {{ $application->person?->person_code }}</div>
                    <div class="col-md-6"><strong>NIC:</strong> {{ $application->person?->national_id }}</div>
                    <div class="col-md-6"><strong>Phone:</strong> {{ $application->person?->phone }}</div>
                </div>
                <a href="{{ route('staff.people.show', $application->person) }}" class="btn btn-sm btn-outline-primary">View Person Profile</a>
            </div></div>

            <div class="card soft-card mb-4"><div class="card-body">
                <h2 class="h5 mb-3">Uploaded Documents</h2>
                @forelse($application->documents as $document)
                    <div class="border-bottom py-2">{{ $document->documentType?->name ?? $document->file_name }} <span class="badge text-bg-secondary">{{ $document->status }}</span></div>
                @empty <div class="text-muted">No documents uploaded.</div> @endforelse
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
                <h2 class="h5 mb-3">Officer Action</h2>
                <form method="POST" action="{{ route('officer.applications.status', $application) }}">
                    @csrf @method('PATCH')
                    <select name="status_id" class="form-select mb-2">
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @selected($application->status_id === $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <textarea name="remarks" rows="3" class="form-control mb-2" placeholder="Department remarks"></textarea>
                    <textarea name="rejection_reason" rows="2" class="form-control mb-2" placeholder="Rejection reason if rejecting"></textarea>
                    <button class="btn btn-primary w-100">Update Status</button>
                </form>
            </div></div>

            <div class="card soft-card"><div class="card-body">
                <h2 class="h5 mb-3">Internal Notes</h2>
                <form method="POST" action="{{ route('officer.applications.notes.store', $application) }}" class="mb-3">
                    @csrf
                    <textarea name="note" rows="3" class="form-control mb-2" placeholder="Add internal note" required></textarea>
                    <button class="btn btn-outline-primary w-100">Add Note</button>
                </form>
                @forelse($application->notes as $note)
                    <div class="border-bottom py-2">{{ $note->note }}<div class="text-muted small">{{ $note->creator?->name }}</div></div>
                @empty <div class="text-muted">No internal notes.</div> @endforelse
            </div></div>
        </div>
    </div>
@endsection
