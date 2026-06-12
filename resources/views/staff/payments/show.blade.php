@extends('layouts.admin')

@section('title', $payment->receipt_no)
@section('page-title', 'Payment Details')

@section('content')
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $payment->receipt_no }}</h1>
            <div class="text-muted">{{ $payment->person?->full_name }} | {{ $payment->application?->application_no }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.payments.receipt', $payment) }}" class="btn btn-outline-success">Print Receipt</a>
            <a href="{{ route('staff.payments.receipt.pdf', $payment) }}" class="btn btn-primary">PDF Receipt</a>
        </div>
    </div>

    <div class="card soft-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><div class="text-muted small">Amount</div><div class="fs-4 fw-bold">{{ number_format($payment->amount, 2) }}</div></div>
                <div class="col-md-4"><div class="text-muted small">Status</div>{{ ucwords(str_replace('_', ' ', $payment->status)) }}</div>
                <div class="col-md-4"><div class="text-muted small">Method</div>{{ $payment->method?->name }}</div>
                <div class="col-md-4"><div class="text-muted small">Payment Date</div>{{ $payment->payment_date?->format('Y-m-d H:i') }}</div>
                <div class="col-md-4"><div class="text-muted small">Received By</div>{{ $payment->receiver?->name }}</div>
                <div class="col-md-4"><div class="text-muted small">Reference</div>{{ $payment->transaction_reference ?: '-' }}</div>
                <div class="col-12"><div class="text-muted small">Remarks</div>{{ $payment->remarks ?: '-' }}</div>
            </div>
        </div>
    </div>
@endsection
