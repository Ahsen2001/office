@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payment Management')

@section('content')
    <form method="GET" class="card soft-card mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(['paid' => 'Paid', 'unpaid' => 'Unpaid', 'partially_paid' => 'Partially Paid', 'refunded' => 'Refunded'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill">Filter</button>
                <a href="{{ route('staff.payments.report.pdf', request()->query()) }}" class="btn btn-success flex-fill">PDF Report</a>
            </div>
        </div>
    </form>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Receipt</th><th>Person</th><th>Application</th><th>Service</th><th>Status</th><th>Amount</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->receipt_no }}</td>
                            <td>{{ $payment->person?->full_name }}</td>
                            <td>{{ $payment->application?->application_no }}</td>
                            <td>{{ $payment->service?->name }}</td>
                            <td><span class="badge text-bg-secondary">{{ ucwords(str_replace('_', ' ', $payment->status)) }}</span></td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_date?->format('Y-m-d') ?? $payment->paid_at?->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('staff.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="{{ route('staff.payments.receipt', $payment) }}" class="btn btn-sm btn-outline-success">Receipt</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $payments->links() }}</div>
    </div>
@endsection
