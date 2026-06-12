@extends('layouts.admin')

@section('title', 'QR Scanner')
@section('page-title', 'QR Scanner')

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card soft-card">
                <div class="card-body">
                    <h1 class="h4 mb-2">Scan QR code</h1>
                    <p class="text-muted">Use a webcam or mobile camera to scan a person card.</p>
                    <div id="reader" class="border rounded overflow-hidden bg-light"></div>
                    <div id="scan-message" class="alert mt-3 d-none"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card soft-card">
                <div class="card-body">
                    <h2 class="h5 mb-3">Manual search</h2>
                    <form id="manual-search-form" class="d-flex gap-2">
                        <input id="manual-code" class="form-control" placeholder="Person ID, QR value, or barcode">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </form>
                    <div class="text-muted small mt-3">Use this if camera access is unavailable or the printed code is damaged.</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const resolveUrl = @json(route('staff.scanner.resolve'));
    const message = document.getElementById('scan-message');
    let handled = false;

    function showMessage(text, type = 'danger') {
        message.className = `alert alert-${type} mt-3`;
        message.textContent = text;
    }

    async function resolveCode(code) {
        if (!code || handled) return;
        handled = true;
        showMessage('Checking code...', 'info');

        try {
            const response = await fetch(`${resolveUrl}?code=${encodeURIComponent(code)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!response.ok) {
                handled = false;
                showMessage(data.message || 'Invalid QR code or barcode.');
                return;
            }

            window.location.href = data.url;
        } catch (error) {
            handled = false;
            showMessage('Unable to read this code. Please try manual search.');
        }
    }

    const scanner = new Html5QrcodeScanner('reader', {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        rememberLastUsedCamera: true,
        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
    }, false);

    scanner.render(resolveCode, () => {});

    document.getElementById('manual-search-form').addEventListener('submit', function (event) {
        event.preventDefault();
        resolveCode(document.getElementById('manual-code').value);
    });
</script>
@endpush
