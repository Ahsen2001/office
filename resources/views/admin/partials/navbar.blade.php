<nav class="admin-topbar d-flex align-items-center px-3 px-lg-4">
    <button class="btn btn-outline-secondary d-lg-none me-3" type="button" data-sidebar-toggle>
        <i class="fa-solid fa-bars"></i>
    </button>
    <div>
        <div class="fw-semibold">@yield('page-title', 'Dashboard')</div>
        <div class="text-muted small">{{ now()->format('l, F j, Y') }}</div>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        @php
            $navNotifications = auth()->check()
                ? \App\Models\OfficeNotification::where('user_id', auth()->id())->latest()->limit(5)->get()
                : collect();
            $unreadNotifications = $navNotifications->where('is_read', false)->count();
        @endphp
        <div class="dropdown">
            <button class="btn btn-light position-relative" data-bs-toggle="dropdown" type="button">
                <i class="fa-regular fa-bell"></i>
                @if($unreadNotifications)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadNotifications }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 320px;">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <strong>Notifications</strong>
                    <form method="POST" action="{{ route('notifications.readAll') }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-link text-decoration-none p-0">Mark all read</button>
                    </form>
                </div>
                @forelse($navNotifications as $notification)
                    <div class="dropdown-item-text border-bottom py-2 {{ $notification->is_read ? 'text-muted' : '' }}">
                        <div class="fw-semibold">{{ $notification->title }}</div>
                        <div class="small">{{ Str::limit($notification->message, 70) }}</div>
                    </div>
                @empty
                    <div class="dropdown-item-text text-muted">No notifications.</div>
                @endforelse
                <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">View all</a>
            </div>
        </div>
        <a class="btn btn-light" href="{{ route('profile.edit') }}"><i class="fa-regular fa-user me-1"></i> Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-danger" type="submit"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</button>
        </form>
    </div>
</nav>
