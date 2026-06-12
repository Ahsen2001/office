@extends('layouts.admin')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
        <form method="GET" class="d-flex gap-2">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search departments">
            <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-search"></i></button>
        </form>
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Add Department</a>
    </div>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Officer</th>
                        <th>Contact</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $department->name }}</div>
                                <div class="text-muted small">{{ $department->location }}</div>
                            </td>
                            <td>{{ $department->code }}</td>
                            <td>{{ $department->officer?->name ?? 'Not assigned' }}</td>
                            <td>
                                <div>{{ $department->phone }}</div>
                                <div class="text-muted small">{{ $department->email }}</div>
                            </td>
                            <td>{{ $department->applications_count }}</td>
                            <td>
                                <span class="badge {{ $department->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ $department->is_active ? route('admin.departments.deactivate', $department) : route('admin.departments.activate', $department) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-warning" type="submit">{{ $department->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="d-inline" onsubmit="return confirm('Delete this department?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No departments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $departments->links() }}</div>
    </div>
@endsection
