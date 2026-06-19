@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports and Analytics')

@push('styles')
<style>
    .report-chart { min-height: 310px; }
    .report-export-card { transition: transform .18s ease, box-shadow .18s ease; }
    .report-export-card:hover { transform: translateY(-2px); box-shadow: var(--office-shadow); }
    @media print {
        .report-filters, .report-exports, .admin-sidebar, .admin-topbar { display: none !important; }
        .admin-main { margin-left: 0 !important; }
        .report-chart { min-height: 240px; }
    }
</style>
@endpush

@section('content')
    <div class="office-page-heading">
        <div>
            <h1 class="h3 mb-1">Operational reports</h1>
            <p class="text-muted mb-0">Scope: <strong>{{ $scopeLabel }}</strong>. Filters apply to cards, charts, tables, and exports.</p>
        </div>
        <div class="ms-xl-auto align-self-xl-center no-print">
            <button type="button" id="print-report" class="btn btn-success d-inline-flex align-items-center justify-content-center">
                <i class="fa-solid fa-print me-2"></i>Print dashboard
            </button>
        </div>
    </div>

    <form method="GET" class="card soft-card mb-4 report-filters">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-sm-6 col-xl-2">
                    <label class="form-label">Date from</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label class="form-label">Date to</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select" @disabled(auth()->user()->isBranchRestricted())>
                        <option value="">All branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($filters['branch_id'] == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @if(auth()->user()->isBranchRestricted())<input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">@endif
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label class="form-label">Status</label>
                    <select name="status_id" class="form-select">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @selected($filters['status_id'] == $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label class="form-label">Officer</label>
                    <select name="officer_id" class="form-select">
                        <option value="">All officers</option>
                        @foreach($officers as $officer)
                            <option value="{{ $officer->id }}" @selected($filters['officer_id'] == $officer->id)>{{ $officer->name }}{{ $officer->branch ? ' · '.$officer->branch->code : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-xl-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Apply</button>
                    <a href="{{ route('reports.index') }}" class="btn btn-light" aria-label="Clear filters"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </div>
            @if($errors->any())<div class="alert alert-danger mt-3 mb-0">{{ $errors->first() }}</div>@endif
        </div>
    </form>

    <div class="row g-3 mb-4">
        @foreach([
            ['People', $summary['people'], 'fa-address-card', ''],
            ['Applications', $summary['applications'], 'fa-folder-open', ''],
            ['Pending', $summary['pending'], 'fa-hourglass-half', ''],
            ['Completed', $summary['completed'], 'fa-circle-check', 'success'],
            ['Rejected', $summary['rejected'], 'fa-circle-xmark', ''],
            ['Delayed', $summary['delayed'], 'fa-triangle-exclamation', ''],
            ['Appointments', $summary['appointments'], 'fa-calendar-check', 'success'],
        ] as [$label, $value, $icon, $class])
            <div class="col-6 col-md-4 col-xl">
                <div class="card soft-card metric-card h-100"><div class="card-body">
                    <div class="metric-icon {{ $class }} mb-3"><i class="fa-solid {{ $icon }}"></i></div>
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fs-3 fw-bold">{{ number_format($value) }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6"><div class="card soft-card h-100"><div class="card-body">
            <h2 class="h5 mb-3">Applications by status</h2>
            <div class="report-chart"><canvas id="statusChart"></canvas></div>
        </div></div></div>
        <div class="col-xl-6"><div class="card soft-card h-100"><div class="card-body">
            <h2 class="h5 mb-3">Branch-wise applications</h2>
            <div class="report-chart"><canvas id="branchChart"></canvas></div>
        </div></div></div>
        <div class="col-xl-6"><div class="card soft-card h-100"><div class="card-body">
            <h2 class="h5 mb-3">Officer workload</h2>
            <div class="report-chart"><canvas id="officerChart"></canvas></div>
        </div></div></div>
        <div class="col-xl-6"><div class="card soft-card h-100"><div class="card-body">
            <h2 class="h5 mb-3">Monthly application trend</h2>
            <div class="report-chart"><canvas id="monthlyChart"></canvas></div>
        </div></div></div>
    </div>

    <div class="card soft-card mb-4 report-exports">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div><h2 class="h5 mb-1">Export reports</h2><p class="text-muted small mb-0">Downloads keep the active date, branch, status, and officer filters.</p></div>
                <span class="badge text-bg-primary">{{ count($availableReports) }} available</span>
            </div>
            <div class="row g-3">
                @foreach($availableReports as $report => $label)
                    <div class="col-md-6 col-xl-4">
                        <div class="border rounded-4 p-3 h-100 report-export-card">
                            <div class="fw-semibold mb-2">{{ $label }}</div>
                            <div class="btn-group btn-group-sm w-100">
                                @foreach(['pdf' => 'PDF', 'excel' => 'Excel', 'csv' => 'CSV'] as $format => $formatLabel)
                                    <a class="btn btn-outline-primary" href="{{ route('reports.export', array_merge(['report' => $report, 'format' => $format], request()->query())) }}">{{ $formatLabel }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card soft-card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 mb-0">Latest matching applications</h2>
            <span class="text-muted small">Showing up to 12</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>No</th><th>Person</th><th>Branch</th><th>Officer</th><th>Status</th><th>Submitted</th></tr></thead>
                <tbody>
                    @forelse($recentApplications as $application)
                        <tr>
                            <td class="fw-semibold">{{ $application->application_no }}</td>
                            <td>{{ $application->person?->full_name }}</td>
                            <td>{{ $application->branch?->name }}</td>
                            <td>{{ $application->assignedOfficer?->name ?? 'Unassigned' }}</td>
                            <td><span class="badge status-{{ $application->status?->code }}">{{ $application->status?->name }}</span></td>
                            <td>{{ $application->submitted_at?->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No applications match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
@endsection

@push('scripts')
<script>
    document.getElementById('print-report')?.addEventListener('click', () => {
        window.print();
    });

    const reportChartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    };
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: @json($applicationsByStatus->keys()), datasets: [{ data: @json($applicationsByStatus->values()), backgroundColor: ['#2563eb','#0ea5e9','#22c55e','#ef4444','#f59e0b','#64748b','#7c3aed'] }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
    new Chart(document.getElementById('branchChart'), {
        type: 'bar',
        data: { labels: @json($applicationsByBranch->keys()), datasets: [{ data: @json($applicationsByBranch->values()), backgroundColor: '#2563eb', borderRadius: 7 }] },
        options: reportChartDefaults
    });
    new Chart(document.getElementById('officerChart'), {
        type: 'bar',
        data: { labels: @json($officerWorkload->keys()), datasets: [{ data: @json($officerWorkload->values()), backgroundColor: '#059669', borderRadius: 7 }] },
        options: reportChartDefaults
    });
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: { labels: @json($monthlyApplications->keys()), datasets: [{ data: @json($monthlyApplications->values()), borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.12)', fill: true, tension: .35 }] },
        options: reportChartDefaults
    });
</script>
@endpush
