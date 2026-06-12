<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 7px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Payment Report</h1>
    <div>Status: {{ $status ? ucwords(str_replace('_', ' ', $status)) : 'All' }} | Date: {{ $dateFrom?->format('Y-m-d') ?? 'Any' }} to {{ $dateTo?->format('Y-m-d') ?? 'Any' }}</div>
    <table>
        <thead><tr><th>Receipt</th><th>Person</th><th>Application</th><th>Service</th><th>Status</th><th>Method</th><th>Amount</th><th>Date</th></tr></thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->receipt_no }}</td>
                    <td>{{ $payment->person?->full_name }}</td>
                    <td>{{ $payment->application?->application_no }}</td>
                    <td>{{ $payment->service?->name }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $payment->status)) }}</td>
                    <td>{{ $payment->method?->name }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->payment_date?->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No payments found.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><th colspan="6">Total</th><th colspan="2">{{ number_format($payments->sum('amount'), 2) }}</th></tr></tfoot>
    </table>
</body>
</html>
