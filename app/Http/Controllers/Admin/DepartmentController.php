<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $departments = Department::with(['officer'])
            ->withCount('applications')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.departments.index', compact('departments', 'search'));
    }

    public function create(): View
    {
        return view('admin.departments.create', [
            'department' => new Department(['is_active' => true]),
            'officers' => $this->officers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $department = Department::create($this->validated($request));
        AuditLogger::log('create', 'departments', "Created department {$department->code}.", $department, null, $department->toArray(), $request);

        return redirect()->route('admin.departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', [
            'department' => $department,
            'officers' => $this->officers(),
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $oldValues = $department->getOriginal();
        $department->update($this->validated($request, $department));
        AuditLogger::log('update', 'departments', "Updated department {$department->code}.", $department, $oldValues, $department->fresh()->toArray(), $request);

        return redirect()->route('admin.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $code = $department->code;
        $department->delete();
        AuditLogger::log('delete', 'departments', "Deleted department {$code}.", $department, ['code' => $code], null, request());

        return redirect()->route('admin.departments.index')->with('success', 'Department deleted successfully.');
    }

    public function activate(Department $department): RedirectResponse
    {
        $department->update(['is_active' => true]);
        AuditLogger::log('activate', 'departments', "Activated department {$department->code}.", $department, ['is_active' => false], ['is_active' => true], request());

        return back()->with('success', 'Department activated successfully.');
    }

    public function deactivate(Department $department): RedirectResponse
    {
        $department->update(['is_active' => false]);
        AuditLogger::log('deactivate', 'departments', "Deactivated department {$department->code}.", $department, ['is_active' => true], ['is_active' => false], request());

        return back()->with('success', 'Department deactivated successfully.');
    }

    private function validated(Request $request, ?Department $department = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:30', Rule::unique('departments', 'code')->ignore($department)],
            'description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'location' => ['nullable', 'string', 'max:180'],
            'department_officer_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function officers()
    {
        return User::where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'department_officer'))
            ->orderBy('name')
            ->get();
    }
}
