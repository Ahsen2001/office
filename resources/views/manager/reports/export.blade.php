<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ ucwords(str_replace('_', ' ', $report)) }} Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>{{ ucwords(str_replace('_', ' ', $report)) }} Report</h1>
    <div>Date: {{ $filters['date_from'] ?: 'Any' }} to {{ $filters['date_to'] ?: 'Any' }}</div>
    <table>
        <thead>
            <tr>
                @foreach(($rows->first() ?? []) as $key => $value)
                    <th>{{ $key }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>@foreach($row as $value)<td>{{ $value }}</td>@endforeach</tr>
            @empty
                <tr><td>No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
