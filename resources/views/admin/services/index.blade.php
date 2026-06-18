@extends('layouts.admin')

@section('title', 'Services')
@section('page-title', 'Services')

@section('content')
    <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
        <form method="GET" class="row g-2 flex-grow-1">
            <div class="col-md-5">
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search services">
            </div>
            <div class="col-md-4">
                <select name="branch_id" class="form-select">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary w-100" type="submit"><i class="fa-solid fa-search me-1"></i> Search</button>
            </div>
        </form>
        @if(auth()->user()->hasRole('admin'))<a href="{{ route('admin.services.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Add Service</a>@endif
    </div>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Service</th>
                        <th>Branch</th>
                        <th>Fee</th>
                        <th>Processing</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $service->name }}</div>
                                <div class="text-muted small">{{ $service->code }}</div>
                            </td>
                            <td>{{ $service->branch?->name }}</td>
                            <td>{{ number_format((float) $service->fee_amount, 2) }}</td>
                            <td>{{ $service->processing_time_days ?? $service->estimated_days ?? '-' }} days</td>
                            <td>
                                <span class="badge {{ $service->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="d-inline" onsubmit="return confirm('Delete this service?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No services found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $services->links() }}</div>
    </div>
@endsection
