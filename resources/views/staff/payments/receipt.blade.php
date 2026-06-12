<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $payment->receipt_no }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; margin: 24px; }
        .receipt { max-width: 760px; margin: 0 auto; border: 1px solid #d1d5db; padding: 24px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 18px; }
        h1 { margin: 0; font-size: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; border-bottom: 1px solid #e5e7eb; padding: 10px; }
        .amount { font-size: 28px; font-weight: 700; }
        .no-print { margin: 16px auto; max-width: 760px; }
        @media print { .no-print { display: none; } body { margin: 0; } .receipt { border: none; } }
    </style>
</head>
<body>
@empty($pdf)
    <div class="no-print">
        <button onclick="window.print()">Print Receipt</button>
    </div>
@endempty
<div class="receipt">
    <div class="header">
        <div>
            <h1>Payment Receipt</h1>
            <div>{{ config('app.name') }}</div>
        </div>
        <div>
            <strong>{{ $payment->receipt_no }}</strong><br>
            {{ $payment->payment_date?->format('Y-m-d H:i') }}
        </div>
    </div>

    <table>
        <tr><th>Person</th><td>{{ $payment->person?->full_name }} ({{ $payment->person?->person_code }})</td></tr>
        <tr><th>Application</th><td>{{ $payment->application?->application_no }}</td></tr>
        <tr><th>Service</th><td>{{ $payment->service?->name }}</td></tr>
        <tr><th>Payment Method</th><td>{{ $payment->method?->name }}</td></tr>
        <tr><th>Status</th><td>{{ ucwords(str_replace('_', ' ', $payment->status)) }}</td></tr>
        <tr><th>Reference</th><td>{{ $payment->transaction_reference ?: '-' }}</td></tr>
        <tr><th>Received By</th><td>{{ $payment->receiver?->name }}</td></tr>
        <tr><th>Remarks</th><td>{{ $payment->remarks ?: '-' }}</td></tr>
        <tr><th>Amount</th><td class="amount">{{ number_format($payment->amount, 2) }}</td></tr>
    </table>
</div>
</body>
</html>
