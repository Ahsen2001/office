@extends('layouts.public')

@section('title', 'Application Status Check - '.config('app.name'))

@section('content')
    <header class="public-page-header">
        <div class="container">
            <span class="public-eyebrow"><i class="fa-solid fa-magnifying-glass"></i> Public application tracking</span>
            <h1 class="mt-2 mb-3">Check your application status</h1>
            <p>Use an application number, person ID, NIC/passport number, or QR code. Only limited public information is displayed.</p>
        </div>
    </header>

    <section class="public-section public-section-soft">
        <div class="container">
            <form method="GET" action="{{ route('public.status') }}" class="public-filter mb-4 no-print" data-status-form>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="application_no" class="form-label fw-semibold">Application number</label>
                        <input id="application_no" name="application_no" value="{{ request('application_no') }}" class="form-control" placeholder="APP-2026-000001">
                    </div>
                    <div class="col-md-4">
                        <label for="person_code" class="form-label fw-semibold">Person ID</label>
                        <input id="person_code" name="person_code" value="{{ request('person_code') }}" class="form-control" placeholder="PER-2026-000001">
                    </div>
                    <div class="col-md-4">
                        <label for="nic" class="form-label fw-semibold">NIC or passport number</label>
                        <input id="nic" name="nic" value="{{ request('nic') }}" class="form-control" placeholder="NIC or passport number">
                    </div>
                    <input type="hidden" name="qr" value="{{ request('qr') }}" data-public-qr-value>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button class="btn btn-public-primary public-btn"><i class="fa-solid fa-magnifying-glass me-1"></i> Check Status</button>
                        <button class="btn btn-outline-success public-btn" type="button" data-start-public-scanner><i class="fa-solid fa-qrcode me-1"></i> Scan QR Code</button>
                        <a href="{{ route('public.status') }}" class="btn btn-outline-secondary public-btn">Clear</a>
                    </div>
                    <div class="col-12 d-none" data-public-scanner-wrap>
                        <div id="public-qr-reader" class="public-map p-2" style="min-height: 300px;"></div>
                        <div class="form-text mt-2">Camera access stays in your browser. The scanned value is submitted only to locate matching applications.</div>
                        <div class="alert alert-danger mt-2 d-none" data-scanner-error></div>
                    </div>
                </div>
            </form>

            <div class="d-flex align-items-start gap-3 mb-4">
                <span class="public-icon green"><i class="fa-solid fa-user-shield"></i></span>
                <div>
                    <h2 class="h6 mb-1">Your privacy is protected</h2>
                    <p class="text-muted mb-0">This page never displays full addresses, personal contact numbers, uploaded files, internal notes, or staff remarks.</p>
                </div>
            </div>

            @if($searched && $applications->isEmpty())
                <div class="alert alert-warning border-0 shadow-sm">No matching application was found. Check your reference details or contact the office for assistance.</div>
            @endif

            <div class="row g-4">
                @foreach($applications as $item)
                    @php
                        $uploaded = $item->documents
                            ->map(fn($document) => strtolower((string) ($document->documentType?->name ?? $document->document_title)))
                            ->filter();
                        $missing = collect($item->required_documents ?? [])
                            ->reject(fn($document) => $uploaded->contains(strtolower(trim((string) $document))))
                            ->values();
                        $appointment = $item->appointments
                            ->whereIn('status', ['scheduled', 'rescheduled'])
                            ->sortByDesc('appointment_date')
                            ->first();
                        $statusClass = in_array($item->status?->code, ['approved', 'completed'], true)
                            ? 'text-bg-success'
                            : (in_array($item->status?->code, ['rejected', 'cancelled'], true) ? 'text-bg-danger' : 'text-bg-primary');
                    @endphp
                    <div class="col-12">
                        <article class="card public-card status-result">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                                    <div>
                                        <div class="service-meta-label">Application number</div>
                                        <h2 class="h4 mb-0">{{ $item->application_no }}</h2>
                                    </div>
                                    <span class="badge {{ $statusClass }} fs-6">{{ $item->status?->name }}</span>
                                </div>
                                <div class="row g-4">
                                    <div class="col-md-6 col-xl-3"><div class="service-meta-label">Service</div><div class="fw-semibold">{{ $item->service?->name }}</div></div>
                                    <div class="col-md-6 col-xl-3"><div class="service-meta-label">Branch</div><div class="fw-semibold">{{ $item->branch?->name }}</div></div>
                                    <div class="col-md-6 col-xl-3"><div class="service-meta-label">Submitted date</div><div class="fw-semibold">{{ $item->submitted_at?->format('Y-m-d') ?? 'Not recorded' }}</div></div>
                                    <div class="col-md-6 col-xl-3"><div class="service-meta-label">Last updated</div><div class="fw-semibold">{{ $item->updated_at?->format('Y-m-d H:i') ?? 'Not recorded' }}</div></div>
                                    <div class="col-md-6"><div class="service-meta-label">Missing documents</div><div class="fw-semibold">{{ $missing->isEmpty() ? 'None currently requested' : $missing->implode(', ') }}</div></div>
                                    <div class="col-md-6"><div class="service-meta-label">Appointment</div><div class="fw-semibold">{{ $appointment ? $appointment->appointment_date?->format('Y-m-d').' at '.$appointment->start_time?->format('H:i') : 'No upcoming appointment' }}</div></div>
                                    <div class="col-md-6">
                                        <div class="service-meta-label">In-charge officer</div>
                                        <div class="fw-semibold">{{ $item->assignedOfficer?->name ?? 'Not assigned' }}</div>
                                        <div class="small text-muted">{{ $item->assignedOfficer?->roles->first()?->name ?? 'Designation not recorded' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="service-meta-label">Officer contact</div>
                                        <div class="fw-semibold">{{ $item->assignedOfficer?->phone ?? $item->branch?->phone ?? 'Contact the branch office' }}</div>
                                        <div class="small text-muted">{{ $item->branch?->name }}</div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.querySelector('[data-start-public-scanner]')?.addEventListener('click', async () => {
        const wrap = document.querySelector('[data-public-scanner-wrap]');
        const qrInput = document.querySelector('[data-public-qr-value]');
        const errorBox = document.querySelector('[data-scanner-error]');

        wrap.classList.remove('d-none');
        errorBox.classList.add('d-none');

        try {
            const scanner = new Html5Qrcode('public-qr-reader');
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                async (decodedText) => {
                    qrInput.value = decodedText;
                    await scanner.stop();
                    document.querySelector('[data-status-form]').submit();
                }
            );
        } catch (error) {
            errorBox.textContent = 'Camera access is unavailable. Use the application number, person ID, or NIC/passport field instead.';
            errorBox.classList.remove('d-none');
        }
    });
</script>
@endpush
