@extends('layouts.admin')
@section('title', $application->application_no)
@section('page-title', 'Application Details')
@section('content')
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
    <div><h1 class="h3 mb-1">{{ $application->application_no }}</h1><div class="text-muted">{{ $application->person?->full_name }} - {{ $application->service?->name }}</div></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('staff.applications.edit', $application) }}">Edit</a><a class="btn btn-outline-secondary" href="{{ route('staff.appointments.create', ['application_id' => $application->id]) }}">Book Appointment</a><a class="btn btn-success" href="{{ route('staff.applications.receipt', $application) }}">Print Receipt</a></div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card soft-card mb-4"><div class="card-body">
            <h2 class="h5 mb-3">Application Information</h2>
            <div class="row g-3">
                <div class="col-md-6"><div class="text-muted small">Person</div><a href="{{ route('staff.people.show', $application->person) }}">{{ $application->person?->full_name }}</a></div>
                <div class="col-md-6"><div class="text-muted small">Status</div><span class="badge text-bg-secondary">{{ $application->status?->name }}</span></div>
                <div class="col-md-6"><div class="text-muted small">Department</div>{{ $application->department?->name }}</div>
                <div class="col-md-6"><div class="text-muted small">Assigned officer</div>{{ $application->assignedOfficer?->name ?? 'Unassigned' }}</div>
                <div class="col-md-6"><div class="text-muted small">Deadline</div>{{ $application->due_date?->format('Y-m-d') }}</div>
                <div class="col-md-6"><div class="text-muted small">Priority</div>{{ ucfirst($application->priority) }}</div>
                <div class="col-12"><div class="text-muted small">Description</div>{{ $application->description }}</div>
                <div class="col-12"><div class="text-muted small">Remarks</div>{{ $application->remarks }}</div>
            </div>
        </div></div>

        <div class="card soft-card mb-4"><div class="card-body">
            <h2 class="h5 mb-3">Notes and Remarks</h2>
            <form method="POST" action="{{ route('staff.applications.notes.store', $application) }}" class="border rounded p-3 mb-3">
                @csrf
                <div class="row g-2">
                    <div class="col-md-3"><input type="text" name="note_type" class="form-control" value="general" required></div>
                    <div class="col-md-3">
                        <select name="visibility" class="form-select" required>
                            <option value="internal">Internal only</option>
                            <option value="department">Department only</option>
                            <option value="public">Public visible</option>
                        </select>
                    </div>
                    <div class="col-md-4"><input type="text" name="note" class="form-control" placeholder="Note message" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Add Note</button></div>
                </div>
            </form>
            @forelse($application->notes as $note)
                <div class="border-bottom py-2">
                    {{ $note->note }}
                    <div class="text-muted small">{{ ucwords(str_replace('_', ' ', $note->note_type)) }} | {{ ucwords(str_replace('_', ' ', $note->visibility)) }} | {{ $note->creator?->name }}</div>
                </div>
            @empty
                <div class="text-muted">No notes recorded.</div>
            @endforelse
        </div></div>

        <div class="card soft-card mb-4"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Documents</h2>
                @if(count($missingRequiredDocuments))
                    <span class="badge text-bg-warning">{{ count($missingRequiredDocuments) }} missing</span>
                @endif
            </div>
            @if(count($missingRequiredDocuments))
                <div class="alert alert-warning py-2"><strong>Missing required documents:</strong> {{ implode(', ', $missingRequiredDocuments) }}</div>
            @endif
            <form method="POST" action="{{ route('staff.documents.store', $application) }}" enctype="multipart/form-data" class="border rounded p-3 mb-3">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Document Type</label>
                        <select name="document_type_id" class="form-select" required>
                            <option value="">Select type</option>
                            @foreach($documentTypes as $documentType)
                                <option value="{{ $documentType->id }}">{{ $documentType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Document Title</label>
                        <input type="text" name="document_title" class="form-control" placeholder="Optional title">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">File</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                    </div>
                    <div class="col-md-9"><input type="text" name="remarks" class="form-control" placeholder="Remarks"></div>
                    <div class="col-md-3"><button class="btn btn-primary w-100">Upload Document</button></div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Document</th><th>Type</th><th>Size</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @forelse($application->documents as $document)
                            <tr>
                                <td>{{ $document->document_title ?? $document->file_name }}<div class="text-muted small">{{ $document->remarks }}</div></td>
                                <td>{{ strtoupper($document->file_type ?? pathinfo($document->file_name, PATHINFO_EXTENSION)) }}</td>
                                <td>{{ $document->file_size ? number_format($document->file_size / 1024, 1).' KB' : '-' }}</td>
                                <td><span class="badge text-bg-secondary">{{ ucfirst($document->status) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('staff.documents.preview', $document) }}" class="btn btn-sm btn-outline-secondary">Preview</a>
                                    <a href="{{ route('staff.documents.download', $document) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                    @if(auth()->user()?->hasRole('admin'))
                                        <form action="{{ route('staff.documents.destroy', $document) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No documents uploaded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>

        <div class="card soft-card mb-4"><div class="card-body">
            <h2 class="h5 mb-3">Payments</h2>
            <form method="POST" action="{{ route('staff.payments.store', $application) }}" class="border rounded p-3 mb-3">
                @csrf
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Amount</label>
                        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $application->total_fee) }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Method</label>
                        <select name="payment_method_id" class="form-select" required>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="partially_paid">Partially Paid</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Payment Date</label>
                        <input type="datetime-local" name="payment_date" class="form-control">
                    </div>
                    <div class="col-md-4"><input type="text" name="transaction_reference" class="form-control" placeholder="Transaction reference"></div>
                    <div class="col-md-5"><input type="text" name="remarks" class="form-control" placeholder="Remarks"></div>
                    <div class="col-md-3"><button class="btn btn-success w-100">Add Payment</button></div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Receipt</th><th>Method</th><th>Status</th><th>Amount</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @forelse($application->payments as $payment)
                            <tr>
                                <td>{{ $payment->receipt_no }}</td>
                                <td>{{ $payment->method?->name }}</td>
                                <td><span class="badge text-bg-secondary">{{ ucwords(str_replace('_', ' ', $payment->status)) }}</span></td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_date?->format('Y-m-d H:i') ?? $payment->paid_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('staff.payments.receipt', $payment) }}" class="btn btn-sm btn-outline-success">Print Receipt</a>
                                    <a href="{{ route('staff.payments.receipt.pdf', $payment) }}" class="btn btn-sm btn-outline-primary">PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>

        <div class="card soft-card"><div class="card-body">
            <h2 class="h5 mb-3">Timeline</h2>
            <div class="timeline">
                @forelse($application->statusHistories as $history)
                    <div class="timeline-item">
                        <div class="fw-semibold">{{ $history->fromStatus?->name ?? 'New' }} to {{ $history->toStatus?->name }}</div>
                        <div class="text-muted small">{{ $history->changed_at?->format('Y-m-d H:i') }} by {{ $history->changedBy?->name }} | {{ $history->department?->name ?? $application->department?->name }}</div>
                        <div>{{ $history->remarks }}</div>
                    </div>
                @empty <div class="text-muted">No timeline entries.</div> @endforelse
            </div>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card soft-card mb-4"><div class="card-body">
            <h2 class="h5 mb-3">Update Status</h2>
            <form method="POST" action="{{ route('staff.applications.status', $application) }}">
                @csrf @method('PATCH')
                <select name="status_id" class="form-select mb-2">@foreach($statuses as $status)<option value="{{ $status->id }}" @selected($application->status_id == $status->id)>{{ $status->name }}</option>@endforeach</select>
                <textarea name="remarks" class="form-control mb-2" rows="3" placeholder="Remarks"></textarea>
                <textarea name="rejection_reason" class="form-control mb-2" rows="2" placeholder="Rejection reason if rejected"></textarea>
                <button class="btn btn-primary w-100">Update</button>
            </form>
        </div></div>
        <div class="card soft-card"><div class="card-body">
            <h2 class="h5 mb-3">Required Documents</h2>
            @forelse($application->required_documents ?? [] as $document)
                <div class="border-bottom py-2 d-flex justify-content-between gap-2">
                    <span>{{ $document }}</span>
                    @if(in_array($document, $missingRequiredDocuments, true))
                        <span class="badge text-bg-warning">Missing</span>
                    @else
                        <span class="badge text-bg-success">Uploaded</span>
                    @endif
                </div>
            @empty<div class="text-muted">No required documents configured.</div>@endforelse
        </div></div>
    </div>
</div>
@endsection
