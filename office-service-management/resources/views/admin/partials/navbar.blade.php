<nav class="admin-topbar d-flex align-items-center px-3 px-lg-4">
    <button class="btn btn-outline-secondary d-lg-none me-3" type="button" data-sidebar-toggle>
        <i class="fa-solid fa-bars"></i>
    </button>
    <div>
        <div class="fw-semibold">@yield('page-title', 'Dashboard')</div>
        <div class="text-muted small">{{ now()->format('l, F j, Y') }}</div>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        <a class="btn btn-light" href="{{ route('profile.edit') }}"><i class="fa-regular fa-user me-1"></i> Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-danger" type="submit"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</button>
        </form>
    </div>
</nav>
