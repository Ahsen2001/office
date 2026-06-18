@extends('layouts.admin')

@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Public contact inbox</h1>
            <p class="text-muted mb-0">Review messages submitted from the public contact page.</p>
        </div>
        <a href="{{ route('public.contact') }}" class="btn btn-outline-primary" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Contact Page</a>
    </div>

    <form method="GET" class="card soft-card mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-6">
                <label for="message-search" class="form-label">Search</label>
                <input id="message-search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Name, email, or subject">
            </div>
            <div class="col-md-3">
                <label for="message-status" class="form-label">Status</label>
                <select id="message-status" name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'closed' => 'Closed'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-sm-6 col-md-1"><a href="{{ route('admin.contact-messages.index') }}" class="btn btn-light w-100" title="Clear filters"><i class="fa-solid fa-rotate-left"></i></a></div>
        </div>
    </form>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Sender</th><th>Subject</th><th>Status</th><th>Received</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr class="{{ $message->status === 'new' ? 'fw-semibold' : '' }}">
                            <td>{{ $message->full_name }}<div class="small text-muted">{{ $message->email }}</div></td>
                            <td>{{ $message->subject }}<div class="small text-muted">{{ Str::limit($message->message, 70) }}</div></td>
                            <td><span class="badge {{ $message->status === 'new' ? 'text-bg-primary' : 'text-bg-light border' }}">{{ ucfirst($message->status) }}</span></td>
                            <td>{{ $message->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="text-end"><a href="{{ route('admin.contact-messages.show', $message) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">No contact messages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="card-body border-top">{{ $messages->links() }}</div>
        @endif
    </div>
@endsection
