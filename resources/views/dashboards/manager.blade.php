@extends('layouts.admin')

@section('title', 'Manager Dashboard')
@section('page-title', 'Manager Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Performance overview</h1>
            <p class="text-muted mb-0">Monitor application volume, pending work, reports, and department performance.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-primary"><i class="fa-solid fa-chart-line me-1"></i> Open Reports</a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card soft-card metric-card h-100">
                <div class="card-body">
                    <div class="metric-icon mb-3"><i class="fa-solid fa-folder-open"></i></div>
                    <div class="text-muted">Applications</div>
                    <div class="fs-3 fw-bold">{{ number_format($applicationsCount) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card soft-card metric-card h-100">
                <div class="card-body">
                    <div class="metric-icon success mb-3"><i class="fa-solid fa-hourglass-half"></i></div>
                    <div class="text-muted">Pending Applications</div>
                    <div class="fs-3 fw-bold">{{ number_format($pendingCount) }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
