@extends('layouts.admin')
@section('title', $branch->name)
@section('page-title', 'Branch Details')
@section('content')
<div class="d-flex justify-content-between align-items-start mb-4"><div><h1 class="h3 mb-1">{{ $branch->name }}</h1><div class="text-muted">{{ $branch->code }} · {{ $branch->location }}</div></div><span class="badge {{ $branch->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span></div>
<div class="row g-3 mb-4">
    @foreach([['Applications',$branch->applications_count,'fa-folder-open'],['Pending',$pendingCount,'fa-hourglass-half'],['Staff',$branch->users->count(),'fa-users'],['Services',$branch->services->count(),'fa-briefcase']] as [$label,$value,$icon])
        <div class="col-sm-6 col-xl-3"><div class="card soft-card h-100"><div class="card-body"><div class="metric-icon mb-3"><i class="fa-solid {{ $icon }}"></i></div><div class="text-muted">{{ $label }}</div><div class="fs-3 fw-bold">{{ $value }}</div></div></div></div>
    @endforeach
</div>
<div class="row g-4">
    <div class="col-xl-4"><div class="card soft-card h-100"><div class="card-body"><h2 class="h5">Branch Information</h2><dl class="row mb-0"><dt class="col-5">Head</dt><dd class="col-7">{{ $branch->head?->name ?? 'Not assigned' }}</dd><dt class="col-5">Phone</dt><dd class="col-7">{{ $branch->phone ?: '-' }}</dd><dt class="col-5">Email</dt><dd class="col-7">{{ $branch->email ?: '-' }}</dd></dl><hr><h3 class="h6">Branch Staff</h3>@forelse($branch->users as $user)<div class="border-bottom py-2">{{ $user->name }}<div class="small text-muted">{{ $user->roles->pluck('name')->join(', ') }}</div></div>@empty<div class="text-muted">No staff assigned.</div>@endforelse</div></div></div>
    <div class="col-xl-8"><div class="card soft-card"><div class="card-body"><h2 class="h5 mb-3">Recent Applications</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>No</th><th>Person</th><th>Service</th><th>Officer</th><th>Status</th></tr></thead><tbody>@forelse($recentApplications as $application)<tr><td>{{ $application->application_no }}</td><td>{{ $application->person?->full_name }}</td><td>{{ $application->service?->name }}</td><td>{{ $application->assignedOfficer?->name ?? 'Unassigned' }}</td><td><span class="badge text-bg-secondary">{{ $application->status?->name }}</span></td></tr>@empty<tr><td colspan="5" class="text-center text-muted">No applications.</td></tr>@endforelse</tbody></table></div></div></div></div>
</div>
@endsection
