@extends('layouts.admin')

@section('title', 'Contact Message')
@section('page-title', 'Contact Message')

@section('content')
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
        <div>
            <a href="{{ route('admin.contact-messages.index') }}" class="text-decoration-none small"><i class="fa-solid fa-arrow-left me-1"></i> Back to messages</a>
            <h1 class="h3 mt-2 mb-1">{{ $contactMessage->subject }}</h1>
            <div class="text-muted">Received {{ $contactMessage->created_at?->format('Y-m-d H:i') }}</div>
        </div>
        <form method="POST" action="{{ route('admin.contact-messages.status', $contactMessage) }}" class="d-flex align-items-start gap-2">
            @csrf
            @method('PATCH')
            <select name="status" class="form-select">
                @foreach(['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'closed' => 'Closed'] as $value => $label)
                    <option value="{{ $value }}" @selected($contactMessage->status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Update</button>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card soft-card">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Message</div>
                    <div style="white-space: pre-wrap;">{{ $contactMessage->message }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card soft-card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Sender details</h2>
                    <div class="mb-3"><div class="text-muted small">Full name</div><div class="fw-semibold">{{ $contactMessage->full_name }}</div></div>
                    <div class="mb-3"><div class="text-muted small">Email</div><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></div>
                    <div class="mb-3"><div class="text-muted small">Phone</div><div>{{ $contactMessage->phone ?: 'Not provided' }}</div></div>
                    <div class="mb-3"><div class="text-muted small">Status</div><span class="badge text-bg-primary">{{ ucfirst($contactMessage->status) }}</span></div>
                    <div><div class="text-muted small">Read by</div><div>{{ $contactMessage->reader?->name ?? 'Not yet recorded' }}</div></div>
                </div>
            </div>
        </div>
    </div>
@endsection
