<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Branch;
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
            'users' => User::with(['branch', 'roles'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['is_active' => true]),
            'roles' => Role::query()
                ->where('is_active', '=', true)
                ->orderBy('name', 'asc')
                ->get(),
            'branches' => Branch::query()
                ->where('is_active', '=', true)
                ->orderBy('name', 'asc')
                ->get(),
            'selectedRole' => null,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'created_by' => $request->user()->id,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active'),
        ]);

        // ensure we always pass an array to sync
        $roleId = Role::where('slug', $validated['role_slug'])->value('id');
        $user->roles()->sync([$roleId]);

        AuditLogger::log('create', 'users', "Created user {$user->email}.", $user, null, $user->only(['name', 'email', 'branch_id', 'is_active']), $request);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::query()
                ->where('is_active', '=', true)
                ->orderBy('name', 'asc')
                ->get(),
            'branches' => Branch::query()
                ->where('is_active', '=', true)
                ->orderBy('name', 'asc')
                ->get(),
            'selectedRole' => $user->roles->first()?->slug,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // capture original values before applying changes
        $oldValues = $user->getOriginal();

        $user->fill([
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // ensure we always pass an array to sync
        $roleId = Role::where('slug', $validated['role_slug'])->value('id');
        $user->roles()->sync([$roleId]);

        AuditLogger::log('update', 'users', "Updated user {$user->email}.", $user, $oldValues, $user->fresh()->toArray(), $request);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $currentUser = request()->user();

        abort_unless($currentUser, 403);
        abort_if((int) $user->getKey() === (int) $currentUser->getKey(), 422, 'You cannot delete your own account.');

        $email = $user->email;
        User::query()
            ->whereKey($user->getKey())
            ->delete();
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
        $currentUser = request()->user();

        abort_unless($currentUser, 403);
        abort_if((int) $user->getKey() === (int) $currentUser->getKey(), 422, 'You cannot deactivate your own account.');

        $user->update(['is_active' => false]);
        AuditLogger::log('deactivate', 'users', "Deactivated user {$user->email}.", $user, ['is_active' => true], ['is_active' => false], request());

        return back()->with('success', 'User deactivated successfully.');
    }
}
