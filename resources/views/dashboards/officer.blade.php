@extends('layouts.app')

@section('title', 'Officer Dashboard')

@section('content')
    <h1 class="h3 mb-1">Department Officer Dashboard</h1>
    <p class="text-muted mb-4">Review and process applications assigned to you.</p>

    <div class="card stat-card">
        <div class="card-body">
            <div class="text-muted">Assigned Applications</div>
            <div class="fs-3 fw-semibold">{{ $assignedCount }}</div>
            <a href="{{ route('officer.applications.index') }}" class="btn btn-outline-primary mt-3">Open Queue</a>
        </div>
    </div>
@endsection
