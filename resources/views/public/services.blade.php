@extends('layouts.public')

@section('title', 'Office Services - '.config('app.name'))

@section('content')
    <header class="public-page-header">
        <div class="container">
            <span class="public-eyebrow"><i class="fa-solid fa-briefcase"></i> Service directory</span>
            <h1 class="mt-2 mb-3">Find the office service you need</h1>
            <p>Review service descriptions, branches, document requirements, processing time, and applicable fees before visiting the office.</p>
        </div>
    </header>

    <section class="public-section public-section-soft">
        <div class="container">
            <form method="GET" class="public-filter mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-6">
                        <label for="service-search" class="form-label fw-semibold">Search services</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input id="service-search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Service name, code, branch, or keyword">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label for="department-filter" class="form-label fw-semibold">Branch</label>
                        <select id="department-filter" name="branch_id" class="form-select">
                            <option value="">All branches</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected($departmentId === $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2"><button class="btn btn-public-primary public-btn w-100">Filter</button></div>
                    <div class="col-sm-6 col-lg-1"><a href="{{ route('public.services') }}" class="btn btn-outline-secondary public-btn w-100" title="Clear filters"><i class="fa-solid fa-rotate-left"></i></a></div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <h2 class="h5 mb-0">{{ $services->total() }} service{{ $services->total() === 1 ? '' : 's' }} available</h2>
                <span class="text-muted small">Fees and processing times may change by office policy.</span>
            </div>

            <div class="row g-4">
                @forelse($services as $service)
                    @php
                        $processingDays = $service->processing_time_days ?? $service->estimated_days;
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <article class="card public-card">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <span class="public-icon"><i class="fa-solid fa-file-signature"></i></span>
                                    <span class="badge text-bg-light border">{{ $service->code }}</span>
                                </div>
                                <h3 class="h5">{{ $service->name }}</h3>
                                <p class="text-muted">{{ $service->description ?: 'Contact the office for a detailed description of this service.' }}</p>

                                <div class="mt-auto">
                                    <div class="mb-3">
                                        <div class="service-meta-label mb-2">Required documents</div>
                                        @if(count($service->required_documents ?? []))
                                            <ul class="small ps-3 mb-0">
                                                @foreach($service->required_documents as $document)
                                                    <li class="mb-1">{{ $document }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="small text-muted">Confirm with the branch before applying.</div>
                                        @endif
                                    </div>
                                    <div class="service-meta">
                                        <div><div class="service-meta-label">Branch</div><div class="small fw-semibold">{{ $service->branch?->name }}</div></div>
                                        <div><div class="service-meta-label">Processing time</div><div class="small fw-semibold">{{ $processingDays ? $processingDays.' days' : 'Contact office' }}</div></div>
                                        <div><div class="service-meta-label">Appointment</div><div class="small fw-semibold">{{ $service->requires_appointment ? 'Required' : 'Not required' }}</div></div>
                                        <div><div class="service-meta-label">Service fee</div><div class="small fw-semibold">LKR {{ number_format((float) $service->fee_amount, 2) }}</div></div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="public-card p-5 text-center">
                            <span class="public-icon mb-3"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <h3 class="h5">No services matched your filters</h3>
                            <p class="text-muted">Try a different keyword or clear the branch filter.</p>
                            <a href="{{ route('public.services') }}" class="btn btn-public-primary public-btn">Clear Filters</a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($services->hasPages())
                <div class="public-pagination mt-4">{{ $services->links() }}</div>
            @endif
        </div>
    </section>
@endsection
