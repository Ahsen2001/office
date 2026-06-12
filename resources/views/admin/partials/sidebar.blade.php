@php
    $links = [
        ['label' => 'Dashboard', 'icon' => 'fa-gauge-high', 'route' => 'admin.dashboard'],
        ['label' => 'Users', 'icon' => 'fa-users-gear', 'route' => 'admin.users.index'],
        ['label' => 'Departments', 'icon' => 'fa-building', 'route' => 'admin.departments.index'],
        ['label' => 'Services', 'icon' => 'fa-briefcase', 'route' => 'admin.services.index'],
        ['label' => 'People', 'icon' => 'fa-address-card', 'route' => 'staff.people.index'],
        ['label' => 'QR Scanner', 'icon' => 'fa-camera', 'route' => 'staff.scanner.index'],
        ['label' => 'Applications', 'icon' => 'fa-folder-open', 'route' => 'staff.applications.index'],
        ['label' => 'Appointments', 'icon' => 'fa-calendar-check', 'route' => 'staff.appointments.index'],
        ['label' => 'Documents', 'icon' => 'fa-file-lines', 'route' => 'staff.documents.index'],
        ['label' => 'Payments', 'icon' => 'fa-money-bill-wave', 'route' => 'staff.payments.index'],
        ['label' => 'Notes', 'icon' => 'fa-note-sticky', 'route' => 'staff.notes.index'],
        ['label' => 'Reports', 'icon' => 'fa-chart-line', 'route' => 'manager.reports.index'],
    ];
@endphp

<aside class="admin-sidebar">
    <div class="brand">
        <div class="metric-icon me-2 bg-white text-primary"><i class="fa-solid fa-qrcode"></i></div>
        <div>
            <div class="fw-bold text-white">Office Service</div>
            <div class="small text-white-50">Admin Panel</div>
        </div>
    </div>
    <nav class="nav flex-column py-3">
        @foreach ($links as $link)
            <a class="nav-link {{ request()->routeIs($link['route']) ? 'active' : '' }}" href="{{ route($link['route']) }}">
                <i class="fa-solid {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
