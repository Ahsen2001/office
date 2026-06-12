<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ServiceApplication;
use Barryvdh\DomPDF\Facade\Pdf;
use Rap2hpoutre\FastExcel\FastExcel;

class ExportController extends Controller
{
    public function applicationsPdf()
    {
        $applications = ServiceApplication::with(['person', 'service', 'status'])->latest()->limit(100)->get();

        return Pdf::loadView('exports.applications', compact('applications'))->download('applications.pdf');
    }

    public function applicationsExcel()
    {
        $applications = ServiceApplication::with(['person', 'service', 'status'])->latest()->get();

        return (new FastExcel($applications))->download('applications.xlsx', function (ServiceApplication $application) {
            return [
                'Application No' => $application->application_no,
                'Person' => $application->person?->full_name,
                'Service' => $application->service?->name,
                'Status' => $application->status?->name,
                'Submitted At' => optional($application->submitted_at)->toDateTimeString(),
            ];
        });
    }
}
