@extends('layouts.admin')
@section('title', 'Application Receipt')
@section('page-title', 'Application Receipt')
@push('styles')<style>@media print{.admin-sidebar,.admin-topbar,.no-print{display:none!important}.admin-main{margin-left:0!important}.receipt{box-shadow:none!important;border:1px solid #ddd}}</style>@endpush
@section('content')
<div class="no-print mb-3"><button onclick="window.print()" class="btn btn-primary">Print Receipt</button> <a href="{{ route('staff.applications.show', $application) }}" class="btn btn-light">Back</a></div>
<div class="card soft-card receipt"><div class="card-body p-4">
    <h1 class="h4">Office Service Application Receipt</h1>
    <hr>
    <div class="row g-3">
        <div class="col-md-6"><strong>Application No:</strong> {{ $application->application_no }}</div>
        <div class="col-md-6"><strong>Date:</strong> {{ $application->submitted_at?->format('Y-m-d H:i') }}</div>
        <div class="col-md-6"><strong>Person:</strong> {{ $application->person?->full_name }}</div>
        <div class="col-md-6"><strong>Person ID:</strong> {{ $application->person?->person_code }}</div>
        <div class="col-md-6"><strong>Service:</strong> {{ $application->service?->name }}</div>
        <div class="col-md-6"><strong>Branch:</strong> {{ $application->branch?->name }}</div>
        <div class="col-md-6"><strong>Status:</strong> {{ $application->status?->name }}</div>
    </div>
</div></div>
@endsection
