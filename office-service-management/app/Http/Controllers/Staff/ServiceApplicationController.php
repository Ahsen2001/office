<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\Service;
use App\Models\ServiceApplication;
use Illuminate\Http\Request;

class ServiceApplicationController extends Controller
{
    public function index()
    {
        return ServiceApplication::with(['person', 'service', 'department', 'assignedOfficer', 'status'])
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'person_id' => ['required', 'exists:people,id'],
            'service_id' => ['required', 'exists:services,id'],
            'assigned_officer_id' => ['nullable', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'subject' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $status = ApplicationStatus::where('code', 'submitted')->firstOrFail();

        $data['application_no'] = 'APP-'.now()->format('Y').'-'.str_pad((string) (ServiceApplication::withTrashed()->count() + 1), 6, '0', STR_PAD_LEFT);
        $data['department_id'] = $service->department_id;
        $data['status_id'] = $status->id;
        $data['submitted_by'] = $request->user()->id;
        $data['submitted_at'] = now();
        $data['total_fee'] = $service->fee_amount;
        $data['priority'] ??= 'normal';

        return ServiceApplication::create($data);
    }

    public function show(ServiceApplication $application)
    {
        return $application->load(['person', 'service', 'department', 'assignedOfficer', 'status', 'documents', 'payments', 'appointments']);
    }
}
