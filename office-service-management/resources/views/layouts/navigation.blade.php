<nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container page-shell">
        <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">{{ config('app.name', 'Office Service') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            @auth
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    @if(auth()->user()->hasRole('admin'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
                    @endif
                    @if(auth()->user()->hasRole('admin', 'staff'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('staff.people.index') }}">People</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('staff.applications.index') }}">Applications</a></li>
                    @endif
                    @if(auth()->user()->hasRole('admin', 'department_officer'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('officer.applications.index') }}">Officer Queue</a></li>
                    @endif
                    @if(auth()->user()->hasRole('admin', 'manager'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('manager.dashboard') }}">Reports</a></li>
                    @endif
                </ul>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ auth()->user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <div class="ms-auto">
                    <a class="btn btn-primary" href="{{ route('login') }}">Login</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
