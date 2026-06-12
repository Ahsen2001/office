@extends('layouts.admin')

@section('title', $person->full_name)
@section('page-title', 'Person Profile')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $person->full_name }}</h1>
            <div class="text-muted">{{ $person->person_code }}</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('staff.people.edit', $person) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1"></i> Edit</a>
            <a href="{{ route('staff.people.card', $person) }}" class="btn btn-success"><i class="fa-solid fa-print me-1"></i> Print Card</a>
            <a href="{{ route('staff.people.qr.download', $person) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-download me-1"></i> QR</a>
            <a href="{{ route('staff.people.barcode.download', $person) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-barcode me-1"></i> Barcode</a>
            @if(auth()->user()->hasRole('admin'))
                <form method="POST" action="{{ route('admin.people.codes.regenerate', $person) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-outline-warning" type="submit"><i class="fa-solid fa-rotate me-1"></i> Regenerate</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card soft-card">
                <div class="card-body text-center">
                    @if($person->photo_path)
                        <img src="{{ Storage::url($person->photo_path) }}" alt="{{ $person->full_name }}" class="rounded-circle object-fit-cover mb-3" style="width: 132px; height: 132px;">
                    @else
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 132px; height: 132px;">
                            <i class="fa-solid fa-user fs-1 text-muted"></i>
                        </div>
                    @endif
                    <h2 class="h5 mb-1">{{ $person->full_name }}</h2>
                    <div class="text-muted mb-3">{{ $person->occupation }}</div>
                    @if($person->qr_code_path)
                        <img src="{{ Storage::url($person->qr_code_path) }}" alt="QR code" class="img-fluid mb-2" style="max-width: 180px;">
                    @endif
                    @if($person->barcode_path)
                        <img src="{{ Storage::url($person->barcode_path) }}" alt="Barcode" class="img-fluid" style="max-width: 220px;">
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card soft-card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Personal Details</h2>
                    <div class="row g-3">
                        <div class="col-md-6"><div class="text-muted small">NIC / Passport</div><div>{{ $person->national_id ?: $person->passport_no }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Date of birth</div><div>{{ $person->date_of_birth?->format('Y-m-d') }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Gender</div><div>{{ ucfirst(str_replace('_', ' ', $person->gender)) }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Contact</div><div>{{ $person->phone }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Email</div><div>{{ $person->email }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">City</div><div>{{ $person->city }}</div></div>
                        <div class="col-12"><div class="text-muted small">Address</div><div>{{ $person->address_line_1 }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Emergency contact</div><div>{{ $person->emergency_contact_name }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Emergency number</div><div>{{ $person->emergency_contact_number }}</div></div>
                        <div class="col-12"><div class="text-muted small">Notes</div><div>{{ $person->notes }}</div></div>
                    </div>
                </div>
            </div>

            <div class="card soft-card">
                <div class="card-body">
                    <h2 class="h5 mb-3">Applications</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>No</th><th>Service</th><th>Department</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($person->applications as $application)
                                    <tr>
                                        <td>{{ $application->application_no }}</td>
                                        <td>{{ $application->service?->name }}</td>
                                        <td>{{ $application->department?->name }}</td>
                                        <td><span class="badge text-bg-secondary">{{ $application->status?->name }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center">No applications found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
