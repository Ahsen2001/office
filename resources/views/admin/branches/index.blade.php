@extends('layouts.admin')
@section('title', 'Branches')
@section('page-title', 'Branch Management')
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search branches">
        <button class="btn btn-outline-primary"><i class="fa-solid fa-search"></i></button>
    </form>
    @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('admin.branches.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Add Branch</a>
    @endif
</div>
<div class="card soft-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Branch</th><th>Head</th><th>Staff</th><th>Applications</th><th>Pending</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($branches as $branch)
                <tr>
                    <td><div class="fw-semibold">{{ $branch->name }}</div><div class="text-muted small">{{ $branch->code }} · {{ $branch->location }}</div></td>
                    <td>{{ $branch->head?->name ?? 'Not assigned' }}</td>
                    <td>{{ $branch->users_count }}</td>
                    <td>{{ $branch->applications_count }}</td>
                    <td><span class="badge text-bg-warning">{{ $branch->pending_applications_count }}</span></td>
                    <td><span class="badge {{ $branch->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ auth()->user()->hasRole('admin') ? route('admin.branches.show', $branch) : route('branches.show', $branch) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ $branch->is_active ? route('admin.branches.deactivate', $branch) : route('admin.branches.activate', $branch) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning">{{ $branch->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                            <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="d-inline" onsubmit="return confirm('Delete this branch?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No branches found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top">{{ $branches->links() }}</div>
</div>
@endsection
