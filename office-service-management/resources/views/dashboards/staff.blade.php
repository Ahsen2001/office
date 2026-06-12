@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
    <h1 class="h3 mb-1">Staff Dashboard</h1>
    <p class="text-muted mb-4">Register people, scan QR/barcodes, and create service applications.</p>

    <div class="row g-3">
        <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="text-muted">Registered People</div><div class="fs-3 fw-semibold">{{ $peopleCount }}</div></div></div></div>
        <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="text-muted">Applications</div><div class="fs-3 fw-semibold">{{ $applicationsCount }}</div></div></div></div>
    </div>
@endsection
