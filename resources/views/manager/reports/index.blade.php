@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports and Analytics')

@section('content')
    <form method="GET" class="card soft-card mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Date From</label><input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Date To</label><input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Department</label><select name="department_id" class="form-select"><option value="">All departments</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected($filters['department_id'] == $department->id)>{{ $department->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Status</label><select name="status_id" class="form-select"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status->id }}" @selected($filters['status_id'] == $status->id)>{{ $status->name }}</option>@endforeach</select></div>
            <div class="col-md-1"><button class="btn btn-primary w-100">Run</button></div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        @foreach([
            ['People', $summary['people'], 'fa-address-card'],
            ['Applications', $summary['applications'], 'fa-folder-open'],
            ['Pending', $summary['pending'], 'fa-hourglass-half'],
            ['Completed', $summary['completed'], 'fa-circle-check'],
            ['Rejected', $summary['rejected'], 'fa-circle-xmark'],
            ['Payments', number_format($summary['payments'], 2), 'fa-money-bill-wave'],
            ['Appointments', $summary['appointments'], 'fa-calendar-check'],
        ] as [$label, $value, $icon])
            <div class="col-sm-6 col-xl-3"><div class="card soft-card h-100"><div class="card-body d-flex align-items-center"><div class="metric-icon me-3"><i class="fa-solid {{ $icon }}"></i></div><div><div class="text-muted small">{{ $label }}</div><div class="fs-4 fw-bold">{{ $value }}</div></div></div></div></div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6"><div class="card soft-card"><div class="card-body"><h2 class="h5 mb-3">Applications by Status</h2><canvas id="statusChart" height="130"></canvas></div></div></div>
        <div class="col-xl-6"><div class="card soft-card"><div class="card-body"><h2 class="h5 mb-3">Department-wise Report</h2><canvas id="departmentChart" height="130"></canvas></div></div></div>
        <div class="col-xl-6"><div class="card soft-card"><div class="card-body"><h2 class="h5 mb-3">Staff-wise Performance</h2><canvas id="staffChart" height="130"></canvas></div></div></div>
        <div class="col-xl-6"><div class="card soft-card"><div class="card-body"><h2 class="h5 mb-3">Appointments by Status</h2><canvas id="appointmentChart" height="130"></canvas></div></div></div>
    </div>

    <div class="card soft-card mb-4"><div class="card-body">
        <h2 class="h5 mb-3">Export Reports</h2>
        <div class="d-flex flex-wrap gap-2">
            @foreach(['people'=>'People Registration','applications'=>'Application','pending'=>'Pending Applications','completed'=>'Completed Works','rejected'=>'Rejected Applications','department'=>'Department-wise','staff'=>'Staff Performance','payments'=>'Payment','appointments'=>'Appointment','daily'=>'Daily','monthly'=>'Monthly'] as $report => $label)
                <div class="btn-group">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('manager.reports.export', array_merge(['report' => $report, 'format' => 'pdf'], request()->query())) }}">{{ $label }} PDF</a>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('manager.reports.export', array_merge(['report' => $report, 'format' => 'excel'], request()->query())) }}">Excel</a>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('manager.reports.export', array_merge(['report' => $report, 'format' => 'csv'], request()->query())) }}">CSV</a>
                </div>
            @endforeach
            <button onclick="window.print()" class="btn btn-success btn-sm">Print Report</button>
        </div>
    </div></div>

    <div class="card soft-card"><div class="card-body">
        <h2 class="h5 mb-3">Recent Applications</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>No</th><th>Person</th><th>Department</th><th>Status</th><th>Submitted</th></tr></thead>
                <tbody>
                    @forelse($recentApplications as $application)
                        <tr><td>{{ $application->application_no }}</td><td>{{ $application->person?->full_name }}</td><td>{{ $application->department?->name }}</td><td>{{ $application->status?->name }}</td><td>{{ $application->submitted_at?->format('Y-m-d') }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
@endsection

@push('scripts')
<script>
    const chartOptions = { responsive: true, plugins: { legend: { display: false } } };
    new Chart(document.getElementById('statusChart'), { type: 'doughnut', data: { labels: @json($applicationsByStatus->keys()), datasets: [{ data: @json($applicationsByStatus->values()), backgroundColor: ['#2563eb','#0ea5e9','#22c55e','#ef4444','#f59e0b','#64748b'] }] } });
    new Chart(document.getElementById('departmentChart'), { type: 'bar', data: { labels: @json($applicationsByDepartment->keys()), datasets: [{ data: @json($applicationsByDepartment->values()), backgroundColor: '#2563eb', borderRadius: 6 }] }, options: chartOptions });
    new Chart(document.getElementById('staffChart'), { type: 'bar', data: { labels: @json($staffPerformance->keys()), datasets: [{ data: @json($staffPerformance->values()), backgroundColor: '#16a34a', borderRadius: 6 }] }, options: chartOptions });
    new Chart(document.getElementById('appointmentChart'), { type: 'pie', data: { labels: @json($appointmentsByStatus->keys()), datasets: [{ data: @json($appointmentsByStatus->values()), backgroundColor: ['#2563eb','#22c55e','#ef4444','#f59e0b','#64748b'] }] } });
</script>
@endpush
