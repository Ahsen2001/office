@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
    <h1 class="h3 mb-1">Manager Dashboard</h1>
    <p class="text-muted mb-4">Monitor performance, revenue, and application volume.</p>

    <div class="row g-3">
        <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="text-muted">Applications</div><div class="fs-3 fw-semibold">{{ $applicationsCount }}</div></div></div></div>
        <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="text-muted">Paid Total</div><div class="fs-3 fw-semibold">{{ number_format($paidTotal, 2) }}</div></div></div></div>
    </div>
@endsection
