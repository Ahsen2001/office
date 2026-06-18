@php
    $user = auth()->user();
    $isAdmin = $user?->hasRole('admin');
    $isStaff = $user?->hasRole('staff');
    $isOfficer = $user?->hasRole('department_officer');
    $isManager = $user?->hasRole('manager');

    $sections = [
        'Workspace' => array_filter([
            $isAdmin ? ['label' => 'Admin Dashboard', 'icon' => 'fa-gauge-high', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'] : null,
            $isStaff ? ['label' => 'Staff Dashboard', 'icon' => 'fa-house-chimney-user', 'route' => 'staff.dashboard', 'active' => 'staff.dashboard'] : null,
            $isOfficer ? ['label' => 'Officer Dashboard', 'icon' => 'fa-clipboard-check', 'route' => 'officer.dashboard', 'active' => 'officer.dashboard'] : null,
            $isManager ? ['label' => 'Manager Dashboard', 'icon' => 'fa-chart-pie', 'route' => 'manager.dashboard', 'active' => 'manager.dashboard'] : null,
            ($isAdmin || $isStaff) ? ['label' => 'People', 'icon' => 'fa-address-card', 'route' => 'staff.people.index', 'active' => 'staff.people.*'] : null,
            ($isAdmin || $isStaff) ? ['label' => 'QR Scanner', 'icon' => 'fa-camera', 'route' => 'staff.scanner.index', 'active' => 'staff.scanner.*'] : null,
        ]),
        'Operations' => array_filter([
            ($isAdmin || $isStaff) ? ['label' => 'Applications', 'icon' => 'fa-folder-open', 'route' => 'staff.applications.index', 'active' => 'staff.applications.*'] : null,
            $isOfficer ? ['label' => 'Department Work', 'icon' => 'fa-list-check', 'route' => 'officer.applications.index', 'active' => 'officer.applications.*'] : null,
            ($isAdmin || $isStaff) ? ['label' => 'Appointments', 'icon' => 'fa-calendar-check', 'route' => 'staff.appointments.index', 'active' => 'staff.appointments.*'] : null,
            ($isAdmin || $isStaff) ? ['label' => 'Documents', 'icon' => 'fa-file-lines', 'route' => 'staff.documents.index', 'active' => 'staff.documents.*'] : null,
            ($isAdmin || $isStaff) ? ['label' => 'Payments', 'icon' => 'fa-money-bill-wave', 'route' => 'staff.payments.index', 'active' => 'staff.payments.*'] : null,
            ($isAdmin || $isStaff) ? ['label' => 'Notes', 'icon' => 'fa-note-sticky', 'route' => 'staff.notes.index', 'active' => 'staff.notes.*'] : null,
        ]),
        'Administration' => array_filter([
            $isAdmin ? ['label' => 'Users', 'icon' => 'fa-users-gear', 'route' => 'admin.users.index', 'active' => 'admin.users.*'] : null,
            $isAdmin ? ['label' => 'Departments', 'icon' => 'fa-building', 'route' => 'admin.departments.index', 'active' => 'admin.departments.*'] : null,
            $isAdmin ? ['label' => 'Services', 'icon' => 'fa-briefcase', 'route' => 'admin.services.index', 'active' => 'admin.services.*'] : null,
            $isAdmin ? ['label' => 'Contact Messages', 'icon' => 'fa-envelope-open-text', 'route' => 'admin.contact-messages.index', 'active' => 'admin.contact-messages.*'] : null,
            $isAdmin ? ['label' => 'Settings', 'icon' => 'fa-gear', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'] : null,
            $isAdmin ? ['label' => 'Audit Logs', 'icon' => 'fa-shield-halved', 'route' => 'admin.audit-logs.index', 'active' => 'admin.audit-logs.*'] : null,
            ($isAdmin || $isManager) ? ['label' => 'Reports', 'icon' => 'fa-chart-line', 'route' => 'manager.reports.index', 'active' => 'manager.reports.*'] : null,
        ]),
    ];
@endphp

<aside class="admin-sidebar">
    <div class="brand">
        <div class="brand-mark me-2"><i class="fa-solid fa-qrcode"></i></div>
        <div>
            <div class="fw-bold">Office Service</div>
            <div class="small text-muted">QR workflow system</div>
        </div>
    </div>
    <nav class="nav flex-column py-3">
        @foreach ($sections as $section => $links)
            @continue(empty($links))
            <div class="sidebar-section">{{ $section }}</div>
            @foreach ($links as $link)
                <a class="nav-link {{ request()->routeIs($link['active']) ? 'active' : '' }}" href="{{ route($link['route']) }}">
                    <i class="fa-solid {{ $link['icon'] }}"></i><span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>
</aside>
