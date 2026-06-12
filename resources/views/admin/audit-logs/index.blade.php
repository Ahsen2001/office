@extends('layouts.admin')

@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')

@section('content')
    <form method="GET" class="card soft-card mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search Logs</label>
                <input type="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Action, module, description, IP">
            </div>
            <div class="col-md-2">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($filters['user_id'] == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Module</label>
                <select name="module" class="form-select">
                    <option value="">All modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" @selected($filters['module'] === $module)>{{ ucwords(str_replace('_', ' ', $module)) }}</option>
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
            <div class="col-md-1 d-grid"><button class="btn btn-primary">Filter</button></div>
            <div class="col-12 d-flex gap-2">
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                <a href="{{ route('admin.audit-logs.export', request()->query()) }}" class="btn btn-success">Export Logs</a>
            </div>
        </div>
    </form>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Date</th><th>User</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td><span class="badge text-bg-secondary">{{ $log->action }}</span></td>
                            <td>{{ ucwords(str_replace('_', ' ', $log->module)) }}</td>
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No audit logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $logs->links() }}</div>
    </div>
@endsection
