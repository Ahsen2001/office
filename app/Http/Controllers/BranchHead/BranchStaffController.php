<?php

namespace App\Http\Controllers\BranchHead;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class BranchStaffController extends Controller
{
    public function index(Request $request): View
    {
        return view('branch-head.staff.index', [
            'staffMembers' => User::query()
                ->where('branch_id', $request->user()->branch_id)
                ->whereHas('roles', fn ($query) => $query->where('slug', 'branch_staff'))
                ->with(['branch', 'roles', 'creator'])
                ->orderBy('name')
                ->paginate(15),
            'branch' => $request->user()->branch,
        ]);
    }

    public function create(Request $request): View
    {
        return view('branch-head.staff.create', [
            'staff' => new User(['branch_id' => $request->user()->branch_id, 'is_active' => true]),
            'branch' => $request->user()->branch,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $role = Role::where('slug', 'branch_staff')->firstOrFail();

        $staff = User::create([
            'branch_id' => $request->user()->branch_id,
            'created_by' => $request->user()->id,
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'designation' => $data['designation'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
            'is_active' => $request->boolean('is_active'),
        ]);
        $staff->roles()->sync([$role->id]);

        AuditLogger::log('create', 'branch_staff', "Created branch staff {$staff->email}.", $staff, null, $staff->only(['name', 'email', 'branch_id', 'designation', 'is_active']), $request);

        return redirect()->route('branch-head.staff.show', $staff)
            ->with('success', 'Branch Staff created successfully.');
    }

    public function show(Request $request, User $user): View
    {
        $this->authorizeStaff($request, $user);

        return view('branch-head.staff.show', [
            'staff' => $user->load(['branch', 'roles', 'creator']),
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeStaff($request, $user);

        return view('branch-head.staff.edit', [
            'staff' => $user,
            'branch' => $request->user()->branch,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeStaff($request, $user);
        $data = $this->validated($request, $user, false);

        $oldValues = $user->only(['name', 'email', 'phone', 'designation', 'is_active']);
        $user->update([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'designation' => $data['designation'],
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::log('update', 'branch_staff', "Updated branch staff {$user->email}.", $user, $oldValues, $user->only(array_keys($oldValues)), $request);

        return redirect()->route('branch-head.staff.show', $user)
            ->with('success', 'Branch Staff updated successfully.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->authorizeStaff($request, $user);
        $user->update(['is_active' => true]);

        return back()->with('success', 'Branch Staff activated.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorizeStaff($request, $user);
        $user->update(['is_active' => false]);

        return back()->with('success', 'Branch Staff deactivated.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeStaff($request, $user);
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);
        AuditLogger::log('password_reset', 'branch_staff', "Reset password for {$user->email}.", $user, null, ['password_reset' => true], $request);

        return back()->with('success', 'Branch Staff password reset successfully.');
    }

    private function validated(Request $request, ?User $staff = null, bool $passwordRequired = true): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($staff)],
            'phone' => ['nullable', 'string', 'max:30'],
            'designation' => ['required', 'string', 'max:150'],
            'role' => ['required', Rule::in(['branch_staff'])],
            'branch_id' => ['required', Rule::in([(string) $request->user()->branch_id, $request->user()->branch_id])],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'confirmed', Password::defaults()],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function authorizeStaff(Request $request, User $user): void
    {
        abort_unless(
            (int) $user->branch_id === (int) $request->user()->branch_id
            && $user->hasRole('branch_staff'),
            403
        );
    }
}
