<nav class="admin-topbar d-flex flex-wrap align-items-center gap-3 px-3 px-lg-4 py-3">
    <button class="btn btn-outline-secondary d-lg-none" type="button" data-sidebar-toggle aria-label="Open menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div class="me-auto">
        <div class="fw-semibold">@yield('page-title', 'Dashboard')</div>
        <div class="page-kicker">{{ now()->format('l, F j, Y') }}</div>
    </div>
    <form method="GET" action="{{ route('search.index') }}" class="position-relative d-none d-lg-block" style="width: min(460px, 34vw);">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search people, IDs, applications..." autocomplete="off" data-global-search>
        </div>
        <div class="list-group position-absolute shadow border-0 w-100 d-none search-suggestions mt-2" data-search-suggestions></div>
    </form>
    <div class="d-flex align-items-center gap-2">
        @php
            $navNotifications = auth()->check()
                ? \App\Models\OfficeNotification::where('user_id', auth()->id())->latest()->limit(5)->get()
                : collect();
            $unreadNotifications = $navNotifications->where('is_read', false)->count();
        @endphp
        <div class="dropdown">
            <button class="btn btn-light position-relative" data-bs-toggle="dropdown" type="button" aria-label="Notifications">
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
        <a class="btn btn-light d-none d-sm-inline-flex align-items-center" href="{{ route('profile.edit') }}"><i class="fa-regular fa-user me-1"></i> Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-danger" type="submit"><i class="fa-solid fa-right-from-bracket me-1"></i><span class="d-none d-sm-inline">Logout</span></button>
        </form>
    </div>
    <form method="GET" action="{{ route('search.index') }}" class="position-relative d-lg-none w-100">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search records..." autocomplete="off" data-global-search-mobile>
        </div>
    </form>
</nav>

@push('scripts')
<script>
    (() => {
        const input = document.querySelector('[data-global-search]');
        const box = document.querySelector('[data-search-suggestions]');
        if (!input || !box) return;

        function renderSuggestions(items) {
            box.replaceChildren();

            if (!items.length) {
                const empty = document.createElement('div');
                empty.className = 'list-group-item text-muted';
                empty.textContent = 'No suggestions found';
                box.appendChild(empty);
                return;
            }

            items.forEach((item) => {
                const link = document.createElement('a');
                link.className = 'list-group-item list-group-item-action';
                link.href = item.url;

                const label = document.createElement('div');
                label.className = 'fw-semibold';
                label.textContent = item.label;

                const meta = document.createElement('div');
                meta.className = 'small text-muted';
                meta.textContent = `${item.type} - ${item.meta ?? ''}`;

                link.append(label, meta);
                box.appendChild(link);
            });
        }

        let timer;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            const query = input.value.trim();
            if (query.length < 2) {
                box.classList.add('d-none');
                box.innerHTML = '';
                return;
            }

            timer = setTimeout(async () => {
                const response = await fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const suggestions = await response.json();
                renderSuggestions(suggestions);
                box.classList.remove('d-none');
            }, 200);
        });

        document.addEventListener('click', (event) => {
            if (!box.contains(event.target) && event.target !== input) {
                box.classList.add('d-none');
            }
        });
    })();
</script>
@endpush
