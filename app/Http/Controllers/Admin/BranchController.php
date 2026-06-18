<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        $branches = Branch::query()
            ->with(['head'])
            ->withCount(['users', 'applications'])
            ->withCount(['applications as pending_applications_count' => fn ($query) => $query
                ->whereHas('status', fn ($status) => $status->whereIn('code', [
                    'submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents',
                ]))])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.branches.index', compact('branches', 'search'));
    }

    public function show(Request $request, Branch $branch): View
    {
        abort_unless($branch->newQuery()->visibleTo($request->user())->whereKey($branch)->exists(), 403);

        return view('admin.branches.show', [
            'branch' => $branch->load(['head', 'users.roles', 'services'])
                ->loadCount(['applications']),
            'pendingCount' => $branch->applications()->whereHas('status', fn ($query) => $query->whereIn('code', [
                'submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents',
            ]))->count(),
            'recentApplications' => $branch->applications()->with(['person', 'service', 'status', 'assignedOfficer'])->latest()->limit(10)->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.branches.create', [
            'branch' => new Branch(['is_active' => true]),
            'heads' => $this->heads(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $branch = Branch::create($this->validated($request));
        $this->syncHead($branch);
        AuditLogger::log('create', 'branches', "Created branch {$branch->code}.", $branch, null, $branch->toArray(), $request);

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch): View
    {
        return view('admin.branches.edit', [
            'branch' => $branch,
            'heads' => $this->heads($branch),
        ]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $oldValues = $branch->getOriginal();
        $branch->update($this->validated($request, $branch));
        $this->syncHead($branch);
        AuditLogger::log('update', 'branches', "Updated branch {$branch->code}.", $branch, $oldValues, $branch->fresh()->toArray(), $request);

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        abort_if($branch->applications()->exists() || $branch->users()->exists(), 422, 'Reassign branch users and applications before deleting this branch.');
        $code = $branch->code;
        $branch->delete();
        AuditLogger::log('delete', 'branches', "Deleted branch {$code}.", $branch, ['code' => $code], null, request());

        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted successfully.');
    }

    public function activate(Branch $branch): RedirectResponse
    {
        $branch->update(['is_active' => true]);
        return back()->with('success', 'Branch activated successfully.');
    }

    public function deactivate(Branch $branch): RedirectResponse
    {
        $branch->update(['is_active' => false]);
        return back()->with('success', 'Branch deactivated successfully.');
    }

    private function validated(Request $request, ?Branch $branch = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:30', Rule::unique('branches', 'code')->ignore($branch)],
            'description' => ['nullable', 'string'],
            'branch_head_user_id' => ['nullable', 'exists:users,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'location' => ['nullable', 'string', 'max:180'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function heads(?Branch $branch = null)
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'branch_head'))
            ->where(fn ($query) => $query->whereNull('branch_id')->when($branch, fn ($query) => $query->orWhere('branch_id', $branch->id)))
            ->orderBy('name')
            ->get();
    }

    private function syncHead(Branch $branch): void
    {
        if ($branch->branch_head_user_id) {
            User::whereKey($branch->branch_head_user_id)->update(['branch_id' => $branch->id]);
        }
    }
}
