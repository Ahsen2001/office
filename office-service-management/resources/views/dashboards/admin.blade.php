@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Admin Dashboard</h1>
            <p class="text-muted mb-0">Manage users, roles, departments, and system access.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a>
    </div>

    <div class="row g-3">
        <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">Users</div><div class="fs-3 fw-semibold">{{ $users }}</div></div></div></div>
        <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">Departments</div><div class="fs-3 fw-semibold">{{ $departments }}</div></div></div></div>
        <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">People</div><div class="fs-3 fw-semibold">{{ $people }}</div></div></div></div>
        <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">Applications</div><div class="fs-3 fw-semibold">{{ $applications }}</div></div></div></div>
    </div>
@endsection
