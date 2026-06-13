<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with(['department', 'roles'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['is_active' => true]),
            'roles' => Role::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'selectedRoles' => [],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'department_id' => $validated['department_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active'),
        ]);

        // ensure we always pass an array to sync
        $roles = $validated['roles'] ?? [];
        $user->roles()->sync($roles);

        AuditLogger::log('create', 'users', "Created user {$user->email}.", $user, null, $user->only(['name', 'email', 'department_id', 'is_active']), $request);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'selectedRoles' => $user->roles->pluck('id')->all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // capture original values before applying changes
        $oldValues = $user->getOriginal();

        $user->fill([
            'department_id' => $validated['department_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // ensure we always pass an array to sync
        $roles = $validated['roles'] ?? [];
        $user->roles()->sync($roles);

        AuditLogger::log('update', 'users', "Updated user {$user->email}.", $user, $oldValues, $user->fresh()->toArray(), $request);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // use id comparison to avoid calling methods on null auth user
        abort_if($user->id === auth()->id(), 422, 'You cannot delete your own account.');

        $email = $user->email;
        $user->delete();
        AuditLogger::log('delete', 'users', "Deleted user {$email}.", $user, ['email' => $email], null, request());

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function activate(User $user): RedirectResponse
    {
        $user->update(['is_active' => true]);
        AuditLogger::log('activate', 'users', "Activated user {$user->email}.", $user, ['is_active' => false], ['is_active' => true], request());

        return back()->with('success', 'User activated successfully.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 422, 'You cannot deactivate your own account.');

        $user->update(['is_active' => false]);
        AuditLogger::log('deactivate', 'users', "Deactivated user {$user->email}.", $user, ['is_active' => true], ['is_active' => false], request());

        return back()->with('success', 'User deactivated successfully.');
    }
}
