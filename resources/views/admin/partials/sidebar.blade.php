@php
    $user = auth()->user();
    $isAdmin = $user?->hasRole('admin');
    $isManagement = $user?->hasRole('management');
    $isReception = $user?->hasRole('reception');
    $isBranchHead = $user?->hasRole('branch_head');
    $isBranchStaff = $user?->hasRole('branch_staff');
    $isBranchUser = $isBranchHead || $isBranchStaff;
    $canOperate = $isAdmin || $isReception || $isBranchUser;

    $sections = [
        'Workspace' => array_filter([
            $isAdmin ? ['label' => 'Admin Dashboard', 'icon' => 'fa-gauge-high', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'] : null,
            $isManagement ? ['label' => 'Management Dashboard', 'icon' => 'fa-chart-pie', 'route' => 'management.dashboard', 'active' => 'management.dashboard'] : null,
            $isReception ? ['label' => 'Reception Dashboard', 'icon' => 'fa-house-chimney-user', 'route' => 'reception.dashboard', 'active' => 'reception.dashboard'] : null,
            $isBranchHead ? ['label' => 'Branch Head Dashboard', 'icon' => 'fa-user-tie', 'route' => 'branch-head.dashboard', 'active' => 'branch-head.dashboard'] : null,
            $isBranchStaff ? ['label' => 'Branch Staff Dashboard', 'icon' => 'fa-clipboard-check', 'route' => 'branch-staff.dashboard', 'active' => 'branch-staff.dashboard'] : null,
            ($isAdmin || $isReception) ? ['label' => 'People', 'icon' => 'fa-address-card', 'route' => 'staff.people.index', 'active' => 'staff.people.*'] : null,
            $canOperate ? ['label' => 'QR Scanner', 'icon' => 'fa-camera', 'route' => 'staff.scanner.index', 'active' => 'staff.scanner.*'] : null,
        ]),
        'Operations' => array_filter([
            $canOperate ? ['label' => 'Applications', 'icon' => 'fa-folder-open', 'route' => 'staff.applications.index', 'active' => 'staff.applications.*'] : null,
            $canOperate ? ['label' => 'Appointments', 'icon' => 'fa-calendar-check', 'route' => 'staff.appointments.index', 'active' => 'staff.appointments.*'] : null,
            $canOperate ? ['label' => 'Documents', 'icon' => 'fa-file-lines', 'route' => 'staff.documents.index', 'active' => 'staff.documents.*'] : null,
            $canOperate ? ['label' => 'Notes', 'icon' => 'fa-note-sticky', 'route' => 'staff.notes.index', 'active' => 'staff.notes.*'] : null,
        ]),
        'Administration' => array_filter([
            $isAdmin ? ['label' => 'Users', 'icon' => 'fa-users-gear', 'route' => 'admin.users.index', 'active' => 'admin.users.*'] : null,
            ($isAdmin || $isManagement || $isBranchUser) ? ['label' => 'Branches', 'icon' => 'fa-building', 'route' => $isAdmin ? 'admin.branches.index' : 'branches.index', 'active' => '*branches*'] : null,
            ($isAdmin || $isManagement || $isBranchUser) ? ['label' => 'Services', 'icon' => 'fa-briefcase', 'route' => $isAdmin ? 'admin.services.index' : 'office.services.index', 'active' => '*services*'] : null,
            $isAdmin ? ['label' => 'Contact Messages', 'icon' => 'fa-envelope-open-text', 'route' => 'admin.contact-messages.index', 'active' => 'admin.contact-messages.*'] : null,
            $isAdmin ? ['label' => 'Settings', 'icon' => 'fa-gear', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'] : null,
            $isAdmin ? ['label' => 'Audit Logs', 'icon' => 'fa-shield-halved', 'route' => 'admin.audit-logs.index', 'active' => 'admin.audit-logs.*'] : null,
            $isManagement ? ['label' => 'Reports', 'icon' => 'fa-chart-line', 'route' => 'management.reports.index', 'active' => 'management.reports.*'] : null,
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
