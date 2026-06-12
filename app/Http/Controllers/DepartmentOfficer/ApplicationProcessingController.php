<?php

namespace App\Http\Controllers\DepartmentOfficer;

use App\Http\Controllers\Controller;
use App\Models\ApplicationNote;
use App\Models\ApplicationStatus;
use App\Models\ApplicationStatusHistory;
use App\Models\ServiceApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationProcessingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $departmentId = $request->user()->department_id;

        $applications = ServiceApplication::with(['person', 'service', 'status', 'assignedOfficer'])
            ->where('department_id', $departmentId)
            ->when($status, fn ($query) => $query->whereHas('status', fn ($statusQuery) => $statusQuery->where('code', $status)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('officer.applications.index', [
            'applications' => $applications,
            'status' => $status,
        ]);
    }

    public function show(Request $request, ServiceApplication $application): View
    {
        $this->authorizeDepartment($request, $application);

        return view('officer.applications.show', [
            'application' => $application->load([
                'person',
                'service',
                'department',
                'assignedOfficer',
                'status',
                'documents.documentType',
                'notes.creator',
                'statusHistories.fromStatus',
                'statusHistories.toStatus',
                'statusHistories.changedBy',
                'statusHistories.department',
            ]),
            'statuses' => ApplicationStatus::whereIn('code', $this->allowedStatuses())->orderBy('sort_order')->get(),
            'missingRequiredDocuments' => $this->missingRequiredDocuments($application),
        ]);
    }

    public function updateStatus(Request $request, ServiceApplication $application): RedirectResponse
    {
        $this->authorizeDepartment($request, $application);

        $allowedStatusIds = ApplicationStatus::whereIn('code', $this->allowedStatuses())->pluck('id')->all();
        $data = $request->validate([
            'status_id' => ['required', 'in:'.implode(',', $allowedStatusIds)],
            'remarks' => ['nullable', 'string'],
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $oldStatusId = $application->status_id;
        $status = ApplicationStatus::findOrFail($data['status_id']);

        $timestamps = [];
        if (in_array($status->code, ['approved', 'rejected', 'completed', 'cancelled'], true)) {
            $timestamps[$status->code.'_at'] = now();
        }

        $application->update([
            'status_id' => $status->id,
            'remarks' => $data['remarks'] ?? $application->remarks,
            'rejection_reason' => $data['rejection_reason'] ?? $application->rejection_reason,
        ] + $timestamps);

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'department_id' => $application->department_id,
            'from_status_id' => $oldStatusId,
            'to_status_id' => $status->id,
            'changed_by' => $request->user()->id,
            'remarks' => $data['remarks'] ?? null,
            'changed_at' => now(),
        ]);

        return back()->with('success', 'Application updated successfully.');
    }

    public function addNote(Request $request, ServiceApplication $application): RedirectResponse
    {
        $this->authorizeDepartment($request, $application);

        $data = $request->validate([
            'note' => ['required', 'string'],
        ]);

        ApplicationNote::create([
            'application_id' => $application->id,
            'person_id' => $application->person_id,
            'created_by' => $request->user()->id,
            'visibility' => 'internal',
            'note' => $data['note'],
        ]);

        return back()->with('success', 'Internal note added successfully.');
    }

    private function authorizeDepartment(Request $request, ServiceApplication $application): void
    {
        abort_unless($request->user()->department_id && (int) $application->department_id === (int) $request->user()->department_id, 403);
    }

    private function allowedStatuses(): array
    {
        return [
            'under_review',
            'processing',
            'waiting_for_documents',
            'approved',
            'rejected',
            'completed',
        ];
    }

    private function missingRequiredDocuments(ServiceApplication $application): array
    {
        $requiredDocuments = collect($application->required_documents ?? [])
            ->map(fn ($document) => trim((string) $document))
            ->filter()
            ->values();

        $uploadedDocuments = $application->documents
            ->map(fn ($document) => strtolower((string) ($document->documentType?->name ?? $document->document_title)))
            ->filter()
            ->values();

        return $requiredDocuments
            ->reject(fn ($document) => $uploadedDocuments->contains(strtolower($document)))
            ->values()
            ->all();
    }
}
