<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Applications Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Service Applications Report</h1>
    <table>
        <thead>
            <tr>
                <th>Application No</th>
                <th>Person</th>
                <th>Service</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($applications as $application)
                <tr>
                    <td>{{ $application->application_no }}</td>
                    <td>{{ $application->person?->full_name }}</td>
                    <td>{{ $application->service?->name }}</td>
                    <td>{{ $application->status?->name }}</td>
                    <td>{{ $application->submitted_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
