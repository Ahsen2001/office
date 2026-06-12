@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="phone">Phone</label>
        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="department_id">Department</label>
        <select id="department_id" name="department_id" class="form-select">
            <option value="">No department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password">Password</label>
        <input id="password" type="password" name="password" class="form-control" @if(! $user->exists) required @endif>
        @if($user->exists)<div class="form-text">Leave blank to keep the current password.</div>@endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" @if(! $user->exists) required @endif>
    </div>
    <div class="col-12">
        <label class="form-label">Roles</label>
        <div class="d-flex flex-wrap gap-3">
            @foreach ($roles as $role)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}" @checked(in_array($role->id, old('roles', $selectedRoles)))>
                    <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->is_active))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Save User</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
</div>
