@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf @method('PATCH')
            <button class="btn btn-outline-primary">Mark All as Read</button>
        </form>
    </div>

    <div class="card soft-card">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <div class="list-group-item {{ $notification->is_read ? '' : 'bg-light' }}">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">{{ $notification->title }}</div>
                            <div>{{ $notification->message }}</div>
                            <div class="text-muted small">{{ ucwords(str_replace('_', ' ', $notification->type)) }} | {{ $notification->created_at?->format('Y-m-d H:i') }}</div>
                        </div>
                        @unless($notification->is_read)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-success">Mark as Read</button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="list-group-item text-muted">No notifications found.</div>
            @endforelse
        </div>
        <div class="card-body border-top">{{ $notifications->links() }}</div>
    </div>
@endsection
