<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Service;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $departmentId = $request->integer('department_id') ?: null;

        $services = Service::with('department')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.services.index', [
            'services' => $services,
            'search' => $search,
            'departmentId' => $departmentId,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'service' => new Service(['is_active' => true, 'fee_amount' => 0]),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'requiredDocuments' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $service = Service::create($this->validated($request));
        AuditLogger::log('create', 'services', "Created service {$service->code}.", $service, null, $service->toArray(), $request);

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'requiredDocuments' => $service->required_documents ?? [],
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $oldValues = $service->getOriginal();
        $service->update($this->validated($request, $service));
        AuditLogger::log('update', 'services', "Updated service {$service->code}.", $service, $oldValues, $service->fresh()->toArray(), $request);

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $code = $service->code;
        $service->delete();
        AuditLogger::log('delete', 'services', "Deleted service {$code}.", $service, ['code' => $code], null, request());

        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }

    private function validated(Request $request, ?Service $service = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'code' => ['required', 'string', 'max:40', Rule::unique('services', 'code')->ignore($service)],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['nullable', 'string'],
            'required_documents' => ['nullable', 'string'],
            'fee_amount' => ['required', 'numeric', 'min:0'],
            'processing_time_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['required_documents'] = collect(preg_split('/\r\n|\r|\n/', $data['required_documents'] ?? ''))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
        $data['estimated_days'] = $data['processing_time_days'];
        $data['requires_payment'] = (float) $data['fee_amount'] > 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
