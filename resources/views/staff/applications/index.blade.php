@extends('layouts.admin')

@section('title', 'Applications')
@section('page-title', 'Service Applications')

@section('content')
    <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
        <form method="GET" class="row g-2 flex-grow-1">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Application no, person, NIC">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status_id" class="form-select">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @selected($statusId == $status->id)>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">All branches</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected($departmentId == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-primary w-100">Filter</button></div>
        </form>
        @if(auth()->user()->hasRole('admin', 'reception'))<a href="{{ route('staff.applications.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Create Application</a>@endif
    </div>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>No</th><th>Person</th><th>Service</th><th>Branch</th><th>Status</th><th>Priority</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($applications as $application)
                        <tr>
                            <td>{{ $application->application_no }}</td>
                            <td>{{ $application->person?->full_name }}<div class="text-muted small">{{ $application->person?->national_id }}</div></td>
                            <td>{{ $application->service?->name }}</td>
                            <td>{{ $application->branch?->name }}</td>
                            <td><span class="badge text-bg-secondary">{{ $application->status?->name }}</span></td>
                            <td>{{ ucfirst($application->priority) }}</td>
                            <td class="text-end">
                                <a href="{{ auth()->user()->hasRole('management') ? route('management.applications.show', $application) : route('staff.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @if(auth()->user()->hasRole('admin', 'reception'))
                                <a href="{{ route('staff.applications.edit', $application) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <a href="{{ route('staff.applications.receipt', $application) }}" class="btn btn-sm btn-outline-success">Receipt</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $applications->links() }}</div>
    </div>
@endsection
