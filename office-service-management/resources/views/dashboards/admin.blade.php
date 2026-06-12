@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Registered People', 'value' => $people, 'icon' => 'fa-address-card'],
                ['label' => 'Applications', 'value' => $applications, 'icon' => 'fa-folder-open'],
                ['label' => 'Pending', 'value' => $pendingApplications, 'icon' => 'fa-hourglass-half'],
                ['label' => 'Completed', 'value' => $completedApplications, 'icon' => 'fa-circle-check'],
                ['label' => 'Rejected', 'value' => $rejectedApplications, 'icon' => 'fa-circle-xmark'],
                ['label' => 'Departments', 'value' => $departments, 'icon' => 'fa-building'],
                ['label' => 'Services', 'value' => $services, 'icon' => 'fa-briefcase'],
                ['label' => 'Staff', 'value' => $staff, 'icon' => 'fa-users'],
                ['label' => 'Today Applications', 'value' => $todayApplications, 'icon' => 'fa-calendar-day'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="col-sm-6 col-xl-4 col-xxl-3">
                <div class="card soft-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-icon me-3"><i class="fa-solid {{ $card['icon'] }}"></i></div>
                        <div>
                            <div class="text-muted small">{{ $card['label'] }}</div>
                            <div class="fs-4 fw-bold">{{ number_format($card['value']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Monthly Applications</h2>
                    <canvas id="monthlyApplicationsChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Department-wise Applications</h2>
                    <canvas id="departmentApplicationsChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Recent Applications</h2>
                    <div class="list-group list-group-flush">
                        @forelse ($recentApplications as $application)
                            <div class="list-group-item px-0">
                                <div class="fw-semibold">{{ $application->application_no }}</div>
                                <div class="text-muted small">{{ $application->person?->full_name }} - {{ $application->service?->name }}</div>
                            </div>
                        @empty
                            <div class="text-muted">No applications yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Recent People</h2>
                    <div class="list-group list-group-flush">
                        @forelse ($recentPeople as $person)
                            <div class="list-group-item px-0">
                                <div class="fw-semibold">{{ $person->full_name }}</div>
                                <div class="text-muted small">{{ $person->person_code }}</div>
                            </div>
                        @empty
                            <div class="text-muted">No people registered yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Pending Tasks</h2>
                    <div class="list-group list-group-flush">
                        @forelse ($pendingTasks as $task)
                            <div class="list-group-item px-0">
                                <span class="badge text-bg-warning me-2">{{ $task->status?->name }}</span>
                                <span class="fw-semibold">{{ $task->application_no }}</span>
                                <div class="text-muted small">{{ $task->person?->full_name }}</div>
                            </div>
                        @empty
                            <div class="text-muted">No pending tasks.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const monthlyLabels = @json($monthlyApplications->keys());
    const monthlyValues = @json($monthlyApplications->values());
    const departmentLabels = @json($departmentApplications->keys());
    const departmentValues = @json($departmentApplications->values());

    new Chart(document.getElementById('monthlyApplicationsChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Applications',
                data: monthlyValues,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, .12)',
                fill: true,
                tension: .35
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('departmentApplicationsChart'), {
        type: 'bar',
        data: {
            labels: departmentLabels,
            datasets: [{
                label: 'Applications',
                data: departmentValues,
                backgroundColor: '#0ea5e9',
                borderRadius: 8
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
</script>
@endpush
