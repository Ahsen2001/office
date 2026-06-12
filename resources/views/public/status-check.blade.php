<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Status Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f6f8fb; }
        .status-shell { max-width: 980px; margin: 0 auto; }
        .soft-card { border: 0; box-shadow: 0 12px 30px rgba(15, 23, 42, .08); border-radius: .75rem; }
        @media print { .no-print { display: none !important; } body { background: #fff; } .soft-card { box-shadow: none; border: 1px solid #ddd; } }
    </style>
</head>
<body>
    <main class="status-shell py-4 py-lg-5 px-3">
        <div class="text-center mb-4">
            <h1 class="h3 mb-2">Application Status Check</h1>
            <p class="text-muted mb-0">Check using application number, person ID, NIC number, or QR code.</p>
        </div>

        <form method="GET" class="card soft-card mb-4 no-print">
            <div class="card-body row g-3">
                <div class="col-md-4"><label class="form-label">Application Number</label><input name="application_no" value="{{ request('application_no') }}" class="form-control" placeholder="APP-2026-000001"></div>
                <div class="col-md-4"><label class="form-label">Person ID</label><input name="person_code" value="{{ request('person_code') }}" class="form-control" placeholder="PER-2026-000001"></div>
                <div class="col-md-4"><label class="form-label">NIC Number</label><input name="nic" value="{{ request('nic') }}" class="form-control" placeholder="NIC number"></div>
                <input type="hidden" name="qr" value="{{ request('qr') }}" data-public-qr-value>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Check Status</button>
                    <a href="{{ route('public.status') }}" class="btn btn-outline-secondary">Clear</a>
                    <button class="btn btn-outline-dark" type="button" data-start-public-scanner><i class="fa-solid fa-qrcode me-1"></i>Scan QR Code</button>
                    <a href="{{ route('login') }}" class="btn btn-link ms-auto">Staff Login</a>
                </div>
                <div class="col-12 d-none" data-public-scanner-wrap>
                    <div id="public-qr-reader" class="border rounded p-2 bg-light"></div>
                    <div class="form-text">Camera access stays in your browser. The scanned value is used only to submit this form.</div>
                </div>
            </div>
        </form>

        @if($searched && $applications->isEmpty())
            <div class="alert alert-warning">No matching application was found. Please check the numbers and try again.</div>
        @endif

        @foreach($applications as $item)
            @php
                $required = collect($item->required_documents ?? [])->map(fn($doc) => strtolower(trim((string) $doc)))->filter();
                $uploaded = $item->documents->map(fn($doc) => strtolower((string) ($doc->documentType?->name ?? $doc->document_title)))->filter();
                $missing = collect($item->required_documents ?? [])->reject(fn($doc) => $uploaded->contains(strtolower(trim((string) $doc))))->values();
                $appointment = $item->appointments->sortByDesc('appointment_date')->first();
            @endphp
            <div class="card soft-card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                        <div>
                            <div class="text-muted small">Application Number</div>
                            <h2 class="h4 mb-0">{{ $item->application_no }}</h2>
                        </div>
                        <span class="badge text-bg-primary align-self-start fs-6">{{ $item->status?->name }}</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><div class="text-muted small">Service Name</div>{{ $item->service?->name }}</div>
                        <div class="col-md-6"><div class="text-muted small">Department</div>{{ $item->department?->name }}</div>
                        <div class="col-md-6"><div class="text-muted small">Submitted Date</div>{{ $item->submitted_at?->format('Y-m-d') }}</div>
                        <div class="col-md-6"><div class="text-muted small">Last Updated Date</div>{{ $item->updated_at?->format('Y-m-d H:i') }}</div>
                        <div class="col-md-6"><div class="text-muted small">Missing Required Documents</div>{{ $missing->isEmpty() ? 'None' : $missing->implode(', ') }}</div>
                        <div class="col-md-6"><div class="text-muted small">Appointment Date</div>{{ $appointment ? $appointment->appointment_date?->format('Y-m-d').' '.$appointment->start_time?->format('H:i') : 'No appointment scheduled' }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </main>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        document.querySelector('[data-start-public-scanner]')?.addEventListener('click', async () => {
            const wrap = document.querySelector('[data-public-scanner-wrap]');
            const qrInput = document.querySelector('[data-public-qr-value]');
            wrap.classList.remove('d-none');
            const scanner = new Html5Qrcode('public-qr-reader');
            await scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: 240 }, async (decodedText) => {
                qrInput.value = decodedText;
                await scanner.stop();
                qrInput.closest('form').submit();
            });
        });
    </script>
</body>
</html>
