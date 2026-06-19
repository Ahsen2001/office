@extends('layouts.admin')
@section('title', $staff->name)
@section('page-title', 'Branch Staff Profile')
@section('content')
<div class="office-page-heading">
    <div><h1 class="h3 mb-1">{{ $staff->name }}</h1><p class="text-muted mb-0">{{ $staff->designation ?: 'Branch Officer' }}</p></div>
    <a href="{{ route('branch-head.staff.edit', $staff) }}" class="btn btn-outline-primary">Edit Profile</a>
</div>
<div class="row g-4">
    <div class="col-lg-7"><div class="card soft-card h-100"><div class="card-body p-4">
        <h2 class="h5 mb-4">Officer details</h2>
        <dl class="row mb-0 g-3">
            <dt class="col-sm-4">Officer Name</dt><dd class="col-sm-8">{{ $staff->name }}</dd>
            <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $staff->email }}</dd>
            <dt class="col-sm-4">Contact Number</dt><dd class="col-sm-8">{{ $staff->phone ?: '-' }}</dd>
            <dt class="col-sm-4">Role</dt><dd class="col-sm-8">{{ $staff->roles->first()?->name }}</dd>
            <dt class="col-sm-4">Branch</dt><dd class="col-sm-8">{{ $staff->branch?->name }}</dd>
            <dt class="col-sm-4">Designation</dt><dd class="col-sm-8">{{ $staff->designation ?: '-' }}</dd>
            <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge {{ $staff->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $staff->is_active ? 'Active' : 'Inactive' }}</span></dd>
            <dt class="col-sm-4">Created By</dt><dd class="col-sm-8">{{ $staff->creator?->name ?? 'System' }}</dd>
            <dt class="col-sm-4">Created Date</dt><dd class="col-sm-8">{{ $staff->created_at?->format('Y-m-d H:i') }}</dd>
        </dl>
    </div></div></div>
    <div class="col-lg-5">
        <div class="card soft-card mb-4"><div class="card-body">
            <h2 class="h5 mb-3">Account status</h2>
            @if($staff->is_active)
                <form method="POST" action="{{ route('branch-head.staff.deactivate', $staff) }}">@csrf @method('PATCH')<button class="btn btn-outline-danger w-100">Deactivate Branch Staff</button></form>
            @else
                <form method="POST" action="{{ route('branch-head.staff.activate', $staff) }}">@csrf @method('PATCH')<button class="btn btn-success w-100">Activate Branch Staff</button></form>
            @endif
        </div></div>
        <div class="card soft-card"><div class="card-body">
            <h2 class="h5 mb-3">Reset password</h2>
            <form method="POST" action="{{ route('branch-head.staff.password', $staff) }}">
                @csrf @method('PUT')
                <input type="password" name="password" class="form-control mb-2" placeholder="New password" required>
                <input type="password" name="password_confirmation" class="form-control mb-3" placeholder="Confirm new password" required>
                <button class="btn btn-outline-primary w-100">Reset Password</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
