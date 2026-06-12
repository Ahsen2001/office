@extends('layouts.admin')

@section('title', $person->full_name)
@section('page-title', 'Person Profile Dashboard')

@push('styles')
<style>
    .profile-hero { background: linear-gradient(135deg, #152238, #2563eb); color: #fff; border-radius: 1rem; }
    .timeline { position: relative; padding-left: 1.5rem; }
    .timeline::before { content: ""; position: absolute; left: .35rem; top: .25rem; bottom: .25rem; width: 2px; background: #dbe4f0; }
    .timeline-item { position: relative; padding-bottom: 1rem; }
    .timeline-item::before { content: ""; position: absolute; left: -1.36rem; top: .25rem; width: .75rem; height: .75rem; border-radius: 50%; background: #2563eb; }
    @media print {
        .admin-sidebar, .admin-topbar, .no-print { display: none !important; }
        .admin-main { margin-left: 0 !important; }
        .soft-card, .profile-hero { box-shadow: none !important; border: 1px solid #ddd; }
    }
</style>
@endpush

@section('content')
    <div class="profile-hero p-4 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-md-auto text-center">
                @if($person->photo_path)
                    <img src="{{ Storage::url($person->photo_path) }}" class="rounded-circle object-fit-cover border border-3 border-white" style="width: 130px; height: 130px;" alt="{{ $person->full_name }}">
                @else
                    <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 130px; height: 130px;"><i class="fa-solid fa-user fs-1"></i></div>
                @endif
            </div>
            <div class="col-md">
                <h1 class="h2 mb-1">{{ $person->full_name }}</h1>
                <div class="mb-2">{{ $person->person_code }} | {{ $person->national_id ?: $person->passport_no }}</div>
                <div class="small opacity-75">Registered {{ $person->registered_at?->format('Y-m-d H:i') }} by {{ $person->registrar?->name ?? 'System' }}</div>
            </div>
            <div class="col-md-auto text-center">
                @if($person->qr_code_path)
                    <img src="{{ Storage::url($person->qr_code_path) }}" class="bg-white rounded p-2" style="width: 130px;" alt="QR code">
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4 no-print">
        <a href="{{ route('staff.applications.create', ['person_id' => $person->id]) }}" class="btn btn-primary"><i class="fa-solid fa-file-circle-plus me-1"></i>Create New Application</a>
        <a href="#documents" class="btn btn-outline-secondary"><i class="fa-solid fa-upload me-1"></i>Upload Document</a>
        <a href="#notes" class="btn btn-outline-secondary"><i class="fa-solid fa-note-sticky me-1"></i>Add Note</a>
        <a href="#payments" class="btn btn-outline-secondary"><i class="fa-solid fa-money-bill-wave me-1"></i>Add Payment</a>
        <a href="{{ route('staff.appointments.create', ['person_id' => $person->id]) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-calendar-plus me-1"></i>Book Appointment</a>
        <button onclick="window.print()" class="btn btn-outline-success"><i class="fa-solid fa-print me-1"></i>Print Profile</button>
        <a href="{{ route('staff.people.report', $person) }}" class="btn btn-outline-dark"><i class="fa-solid fa-file-arrow-down me-1"></i>Download Report</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Total applications', $totalApplications, 'fa-folder-open'],
            ['Pending applications', $pendingApplications, 'fa-hourglass-half'],
            ['Completed works', $completedApplications, 'fa-circle-check'],
            ['Rejected applications', $rejectedApplications, 'fa-circle-xmark'],
        ] as [$label, $value, $icon])
            <div class="col-md-3">
                <div class="card soft-card h-100"><div class="card-body"><div class="metric-icon mb-3"><i class="fa-solid {{ $icon }}"></i></div><div class="text-muted">{{ $label }}</div><div class="fs-3 fw-bold">{{ $value }}</div></div></div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card soft-card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Personal Information</h2>
                    <dl class="row mb-0">
                        <dt class="col-5">DOB</dt><dd class="col-7">{{ $person->date_of_birth?->format('Y-m-d') }}</dd>
                        <dt class="col-5">Gender</dt><dd class="col-7">{{ ucfirst(str_replace('_', ' ', $person->gender)) }}</dd>
                        <dt class="col-5">Address</dt><dd class="col-7">{{ $person->address_line_1 }}</dd>
                        <dt class="col-5">City</dt><dd class="col-7">{{ $person->city }}</dd>
                        <dt class="col-5">Phone</dt><dd class="col-7">{{ $person->phone }}</dd>
                        <dt class="col-5">Email</dt><dd class="col-7">{{ $person->email }}</dd>
                        <dt class="col-5">Occupation</dt><dd class="col-7">{{ $person->occupation }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card soft-card" id="payments">
                <div class="card-body">
                    <h2 class="h5 mb-3">Payment Summary</h2>
                    <div class="d-flex justify-content-between"><span>Paid</span><strong>{{ number_format($paidTotal, 2) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Pending</span><strong>{{ number_format($pendingPaymentTotal, 2) }}</strong></div>
                    <hr>
                    @forelse($person->payments as $payment)
                        <div class="small mb-2">{{ $payment->receipt_no }} - {{ number_format($payment->amount, 2) }} <span class="badge text-bg-secondary">{{ $payment->status }}</span></div>
                    @empty
                        <div class="text-muted">No payments recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card soft-card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Applications</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>No</th><th>Service</th><th>Department</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                @forelse($person->applications as $application)
                                    <tr>
                                        <td>{{ $application->application_no }}</td>
                                        <td>{{ $application->service?->name }}</td>
                                        <td>{{ $application->department?->name }}</td>
                                        <td><span class="badge text-bg-secondary">{{ $application->status?->name }}</span></td>
                                        <td><a href="{{ route('staff.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No applications found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6" id="documents">
                    <div class="card soft-card h-100"><div class="card-body">
                        <h2 class="h5 mb-3">Documents</h2>
                        <form method="POST" action="{{ route('staff.people.documents.store', $person) }}" enctype="multipart/form-data" class="no-print border rounded p-3 mb-3">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <select name="document_type_id" class="form-select form-select-sm" required>
                                        <option value="">Document type</option>
                                        @foreach($documentTypes as $documentType)
                                            <option value="{{ $documentType->id }}">{{ $documentType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6"><input type="text" name="document_title" class="form-control form-control-sm" placeholder="Document title"></div>
                                <div class="col-md-8"><input type="file" name="document" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required></div>
                                <div class="col-md-4"><button class="btn btn-sm btn-primary w-100">Upload Document</button></div>
                                <div class="col-12"><input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks"></div>
                            </div>
                        </form>
                        @forelse($person->documents as $document)
                            <div class="border-bottom py-2 d-flex justify-content-between gap-2">
                                <div>{{ $document->document_title ?? $document->documentType?->name ?? $document->file_name }} <span class="badge text-bg-secondary">{{ $document->status }}</span></div>
                                <div class="text-nowrap no-print">
                                    <a href="{{ route('staff.documents.preview', $document) }}" class="btn btn-sm btn-outline-secondary">Preview</a>
                                    <a href="{{ route('staff.documents.download', $document) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                </div>
                            </div>
                        @empty <div class="text-muted">No documents uploaded.</div> @endforelse
                    </div></div>
                </div>
                <div class="col-lg-6" id="appointments">
                    <div class="card soft-card h-100"><div class="card-body">
                        <h2 class="h5 mb-3">Appointment History</h2>
                        @forelse($person->appointments as $appointment)
                            <div class="border-bottom py-2">{{ $appointment->appointment_date?->format('Y-m-d') }} {{ $appointment->start_time }} <span class="badge text-bg-secondary">{{ $appointment->status }}</span></div>
                        @empty <div class="text-muted">No appointments booked.</div> @endforelse
                    </div></div>
                </div>
                <div class="col-lg-6" id="notes">
                    <div class="card soft-card h-100"><div class="card-body">
                        <h2 class="h5 mb-3">Notes and Remarks</h2>
                        <form method="POST" action="{{ route('staff.people.notes.store', $person) }}" class="no-print border rounded p-3 mb-3">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-6"><input type="text" name="note_type" class="form-control form-control-sm" value="general" required></div>
                                <div class="col-md-6">
                                    <select name="visibility" class="form-select form-select-sm" required>
                                        <option value="internal">Internal only</option>
                                        <option value="department">Department only</option>
                                        <option value="public">Public visible</option>
                                    </select>
                                </div>
                                <div class="col-12"><textarea name="note" rows="2" class="form-control form-control-sm" placeholder="Note message" required></textarea></div>
                                <div class="col-12"><button class="btn btn-sm btn-primary w-100">Add Note</button></div>
                            </div>
                        </form>
                        @forelse($person->applicationNotes as $note)
                            <div class="border-bottom py-2">
                                {{ $note->note }}
                                <div class="text-muted small">{{ ucwords(str_replace('_', ' ', $note->note_type)) }} | {{ ucwords(str_replace('_', ' ', $note->visibility)) }} | {{ $note->creator?->name }}</div>
                            </div>
                        @empty <div class="text-muted">{{ $person->notes ?: 'No notes recorded.' }}</div> @endforelse
                    </div></div>
                </div>
                <div class="col-lg-6">
                    <div class="card soft-card h-100"><div class="card-body">
                        <h2 class="h5 mb-3">Application History Timeline</h2>
                        <div class="timeline">
                            @forelse($person->applications->flatMap->statusHistories->sortByDesc('changed_at') as $history)
                                <div class="timeline-item">
                                    <div class="fw-semibold">{{ $history->toStatus?->name }}</div>
                                    <div class="text-muted small">{{ $history->application?->application_no }} | {{ $history->changed_at?->format('Y-m-d H:i') }}</div>
                                    <div>{{ $history->remarks }}</div>
                                </div>
                            @empty <div class="text-muted">No timeline yet.</div> @endforelse
                        </div>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
@endsection
