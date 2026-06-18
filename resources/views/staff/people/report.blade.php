<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $person->person_code }} Profile Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        h1, h2 { margin: 0 0 8px; }
        h1 { font-size: 22px; }
        h2 { font-size: 15px; border-bottom: 1px solid #d1d5db; padding-bottom: 5px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 7px; vertical-align: top; text-align: left; }
        th { background: #f3f4f6; }
        .header { display: table; width: 100%; margin-bottom: 18px; }
        .header-main { display: table-cell; vertical-align: top; }
        .header-code { display: table-cell; vertical-align: top; text-align: right; width: 180px; }
        .muted { color: #6b7280; }
        .summary { display: table; width: 100%; table-layout: fixed; margin: 14px 0; }
        .summary-box { display: table-cell; border: 1px solid #d1d5db; padding: 8px; }
        .summary-box strong { display: block; font-size: 18px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-main">
            <h1>Person Profile Report</h1>
            <div class="muted">Generated {{ now()->format('Y-m-d H:i') }}</div>
        </div>
        <div class="header-code">
            <strong>{{ $person->person_code }}</strong><br>
            {{ $person->full_name }}
        </div>
    </div>

    <h2>Personal Details</h2>
    <table>
        <tr><th>Full Name</th><td>{{ $person->full_name }}</td><th>NIC / Passport</th><td>{{ $person->national_id ?: $person->passport_no }}</td></tr>
        <tr><th>Date of Birth</th><td>{{ $person->date_of_birth?->format('Y-m-d') }}</td><th>Gender</th><td>{{ ucfirst(str_replace('_', ' ', $person->gender)) }}</td></tr>
        <tr><th>Address</th><td>{{ $person->address_line_1 }}, {{ $person->city }}</td><th>Contact</th><td>{{ $person->phone }}<br>{{ $person->email }}</td></tr>
        <tr><th>Occupation</th><td>{{ $person->occupation }}</td><th>Registered</th><td>{{ $person->registered_at?->format('Y-m-d H:i') }}</td></tr>
    </table>

    <div class="summary">
        <div class="summary-box">Total Applications<strong>{{ $totalApplications }}</strong></div>
        <div class="summary-box">Pending<strong>{{ $pendingApplications }}</strong></div>
        <div class="summary-box">Completed<strong>{{ $completedApplications }}</strong></div>
        <div class="summary-box">Rejected<strong>{{ $rejectedApplications }}</strong></div>
    </div>


    <h2>Applications</h2>
    <table>
        <thead><tr><th>No</th><th>Service</th><th>Branch</th><th>Status</th><th>Submitted</th></tr></thead>
        <tbody>
            @forelse($person->applications as $application)
                <tr>
                    <td>{{ $application->application_no }}</td>
                    <td>{{ $application->service?->name }}</td>
                    <td>{{ $application->branch?->name }}</td>
                    <td>{{ $application->status?->name }}</td>
                    <td>{{ $application->submitted_at?->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No applications found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Documents</h2>
    <table>
        <thead><tr><th>Document</th><th>Status</th><th>Uploaded</th></tr></thead>
        <tbody>
            @forelse($person->documents as $document)
                <tr><td>{{ $document->documentType?->name ?? $document->file_name }}</td><td>{{ ucfirst($document->status) }}</td><td>{{ $document->created_at?->format('Y-m-d') }}</td></tr>
            @empty
                <tr><td colspan="3">No documents uploaded.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Notes and Appointment History</h2>
    <table>
        <tr>
            <th>Notes</th>
            <td>
                @forelse($person->applicationNotes as $note)
                    {{ $note->created_at?->format('Y-m-d') }} - {{ $note->note }}<br>
                @empty
                    {{ $person->notes ?: 'No notes recorded.' }}
                @endforelse
            </td>
        </tr>
        <tr>
            <th>Appointments</th>
            <td>
                @forelse($person->appointments as $appointment)
                    {{ $appointment->appointment_no }} - {{ $appointment->appointment_date?->format('Y-m-d') }} {{ $appointment->start_time }} ({{ ucfirst($appointment->status) }})<br>
                @empty
                    No appointments booked.
                @endforelse
            </td>
        </tr>
    </table>
</body>
</html>
