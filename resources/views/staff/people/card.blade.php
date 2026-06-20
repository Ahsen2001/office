@extends('layouts.admin')

@section('title', 'Printable Person Card')
@section('page-title', 'Printable Person Card')

@push('styles')
<style>
    .id-card {
        max-width: 760px;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1rem 2rem rgba(15, 23, 42, .12);
    }
    .id-card-header {
        background: #152238;
        color: #fff;
        padding: 18px 22px;
    }
    @media print {
        body * { visibility: hidden; }
        .print-area, .print-area * { visibility: visible; }
        .print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .admin-sidebar, .admin-topbar, .no-print { display: none !important; }
        .admin-main { margin-left: 0 !important; }
        .id-card { box-shadow: none; border: 1px solid #ddd; }
    }
</style>
@endpush

@section('content')
    <div class="no-print mb-3">
        <button type="button" class="btn btn-primary" data-print-page aria-label="Print person card"><i class="fa-solid fa-print me-1"></i> Print Card</button>
        <a class="btn btn-light" href="{{ route('staff.people.show', $person) }}">Back to profile</a>
    </div>

    <div class="print-area">
        <div class="id-card">
            <div class="id-card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold fs-4">Office Service ID Card</div>
                    <div class="text-white-50">Person Registration Profile</div>
                </div>
                <div class="text-end fw-semibold">{{ $person->person_code }}</div>
            </div>
            <div class="p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-3 text-center">
                        @if($person->photo_path && Storage::disk('public')->exists($person->photo_path))
                            <img src="{{ route('staff.people.photo', $person) }}" alt="{{ $person->full_name }}" class="rounded object-fit-cover" style="width: 140px; height: 160px;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 140px; height: 160px;"><i class="fa-solid fa-user fs-1 text-muted"></i></div>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <h2 class="h4 mb-3">{{ $person->full_name }}</h2>
                        <div><strong>NIC/Passport:</strong> {{ $person->national_id ?: $person->passport_no }}</div>
                        <div><strong>DOB:</strong> {{ $person->date_of_birth?->format('Y-m-d') }}</div>
                        <div><strong>Phone:</strong> {{ $person->phone }}</div>
                        <div><strong>City:</strong> {{ $person->city }}</div>
                        <div><strong>Emergency:</strong> {{ $person->emergency_contact_number }}</div>
                    </div>
                    <div class="col-md-4 text-center">
                        @if($person->qr_code_path)
                            <img src="{{ route('staff.people.qr.view', $person) }}" alt="QR code" class="img-fluid" style="max-width: 170px;">
                        @endif
                        @if($person->barcode_path)
                            <img src="{{ route('staff.people.barcode.view', $person) }}" alt="Barcode" class="img-fluid mt-2">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
