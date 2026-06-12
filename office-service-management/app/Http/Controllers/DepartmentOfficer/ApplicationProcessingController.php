<?php

namespace App\Http\Controllers\DepartmentOfficer;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\ApplicationStatusHistory;
use App\Models\ServiceApplication;
use Illuminate\Http\Request;

class ApplicationProcessingController extends Controller
{
    public function index(Request $request)
    {
        return ServiceApplication::with(['person', 'service', 'status'])
            ->where('assigned_officer_id', $request->user()->id)
            ->latest()
            ->paginate(20);
    }

    public function updateStatus(Request $request, ServiceApplication $application)
    {
        $data = $request->validate([
            'status_code' => ['required', 'exists:application_statuses,code'],
            'remarks' => ['nullable', 'string'],
            'rejection_reason' => ['nullable', 'required_if:status_code,rejected', 'string'],
        ]);

        $newStatus = ApplicationStatus::where('code', $data['status_code'])->firstOrFail();
        $oldStatusId = $application->status_id;

        $application->update([
            'status_id' => $newStatus->id,
            'rejection_reason' => $data['rejection_reason'] ?? $application->rejection_reason,
        ]);

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status_id' => $oldStatusId,
            'to_status_id' => $newStatus->id,
            'changed_by' => $request->user()->id,
            'remarks' => $data['remarks'] ?? null,
            'changed_at' => now(),
        ]);

        return $application->refresh()->load('status');
    }
}
