@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Users</h1>
            <div class="text-muted">Manage office user accounts and roles.</div>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Add User</a>
    </div>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <div class="text-muted small">{{ $user->email }}</div>
                            </td>
                            <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                            <td>{{ $user->department?->name ?? '-' }}</td>
                            <td><span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ $user->is_active ? route('admin.users.deactivate', $user) : route('admin.users.activate', $user) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-warning" type="submit">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $users->links() }}</div>
    </div>
@endsection
