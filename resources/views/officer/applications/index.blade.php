@extends('layouts.admin')

@section('title', 'Officer Applications')
@section('page-title', 'Department Applications')

@section('content')
    <div class="d-flex flex-wrap gap-2 mb-4">
        @foreach(['' => 'All', 'pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'waiting_for_documents' => 'Missing Documents'] as $code => $label)
            <a class="btn {{ $status === $code ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('officer.applications.index', array_filter(['status' => $code])) }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>No</th><th>Person</th><th>Service</th><th>Status</th><th>Officer</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td>{{ $application->application_no }}</td>
                        <td>{{ $application->person?->full_name }}</td>
                        <td>{{ $application->service?->name }}</td>
                        <td><span class="badge text-bg-secondary">{{ $application->status?->name }}</span></td>
                        <td>{{ $application->assignedOfficer?->name ?? 'Unassigned' }}</td>
                        <td class="text-end"><a href="{{ route('officer.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No applications found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $applications->links() }}</div>
    </div>
@endsection
