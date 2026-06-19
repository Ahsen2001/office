@extends('layouts.admin')
@section('title', 'Branch Staff')
@section('page-title', 'Branch Staff Management')
@section('content')
<div class="office-page-heading">
    <div><h1 class="h3 mb-1">{{ $branch->name }} staff</h1><p class="text-muted mb-0">Manage Branch Staff accounts only within your assigned branch.</p></div>
    <a href="{{ route('branch-head.staff.create') }}" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i>Create Branch Staff</a>
</div>
<div class="card soft-card"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Officer</th><th>Designation</th><th>Contact</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse($staffMembers as $staff)
                <tr>
                    <td><div class="fw-semibold">{{ $staff->name }}</div><div class="small text-muted">{{ $staff->email }}</div></td>
                    <td>{{ $staff->designation ?: 'Branch Officer' }}</td>
                    <td>{{ $staff->phone ?: '-' }}</td>
                    <td><span class="badge {{ $staff->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $staff->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>{{ $staff->created_at?->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('branch-head.staff.show', $staff) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="{{ route('branch-head.staff.edit', $staff) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No Branch Staff accounts found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div><div class="card-body border-top">{{ $staffMembers->links() }}</div></div>
@endsection
