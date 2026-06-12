@extends('layouts.admin')

@section('title', 'Officer Dashboard')
@section('page-title', 'Department Officer Dashboard')

@section('content')
    <div class="row g-3 mb-4">
        @foreach([
            ['Assigned Applications', $assignedCount, 'fa-folder-open'],
            ['Pending Applications', $pendingCount, 'fa-hourglass-half'],
            ['Processing Applications', $processingCount, 'fa-spinner'],
            ['Completed Applications', $completedCount, 'fa-circle-check'],
        ] as [$label, $value, $icon])
            <div class="col-md-3"><div class="card soft-card h-100"><div class="card-body"><div class="metric-icon mb-3"><i class="fa-solid {{ $icon }}"></i></div><div class="text-muted">{{ $label }}</div><div class="fs-3 fw-bold">{{ $value }}</div></div></div></div>
        @endforeach
    </div>

    <div class="card soft-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Recent Department Applications</h2>
                <a href="{{ route('officer.applications.index') }}" class="btn btn-outline-primary btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>No</th><th>Person</th><th>Service</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse($recentApplications as $application)
                        <tr>
                            <td>{{ $application->application_no }}</td>
                            <td>{{ $application->person?->full_name }}</td>
                            <td>{{ $application->service?->name }}</td>
                            <td><span class="badge text-bg-secondary">{{ $application->status?->name }}</span></td>
                            <td class="text-end"><a href="{{ route('officer.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No applications assigned to your department.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
