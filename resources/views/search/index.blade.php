@extends('layouts.admin')

@section('title', 'Advanced Search')
@section('page-title', 'Advanced Search')

@section('content')
    <form method="GET" class="card soft-card mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label">Search</label>
                <input type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Name, person ID, NIC/passport, phone, application no">
            </div>
            <div class="col-md-2">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected($filters['department_id'] == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Service</label>
                <select name="service_id" class="form-select">
                    <option value="">All</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected($filters['service_id'] == $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status_id" class="form-select">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @selected($filters['status_id'] == $status->id)>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill">Search</button>
                <a href="{{ route('search.index') }}" class="btn btn-outline-secondary flex-fill">Clear Filters</a>
            </div>
        </div>
    </form>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">People</h2>
                    <div class="list-group list-group-flush">
                        @forelse($people as $person)
                            <a href="{{ route('staff.people.show', $person) }}" class="list-group-item list-group-item-action px-0">
                                <div class="fw-semibold">{{ $person->full_name }}</div>
                                <div class="text-muted small">{{ $person->person_code }} | {{ $person->national_id ?: $person->passport_no }} | {{ $person->phone }}</div>
                            </a>
                        @empty
                            <div class="text-muted">No people found.</div>
                        @endforelse
                    </div>
                </div>
                <div class="card-body border-top">{{ $people->links() }}</div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Applications</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>No</th><th>Person</th><th>Department</th><th>Service</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                @forelse($applications as $application)
                                    <tr>
                                        <td>{{ $application->application_no }}</td>
                                        <td>{{ $application->person?->full_name }}</td>
                                        <td>{{ $application->department?->name }}</td>
                                        <td>{{ $application->service?->name }}</td>
                                        <td><span class="badge text-bg-secondary">{{ $application->status?->name }}</span></td>
                                        <td><a href="{{ route('staff.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No applications found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-body border-top">{{ $applications->links() }}</div>
            </div>
        </div>
    </div>
@endsection
