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
        <label class="form-label" for="branch_id">Branch</label>
        <select id="branch_id" name="branch_id" class="form-select">
            <option value="">No branch / all branches</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', $user->branch_id) == $branch->id)>{{ $branch->name }}</option>
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
        <label class="form-label" for="role_slug">Role</label>
        <select id="role_slug" name="role_slug" class="form-select" required>
            <option value="">Select role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->slug }}" @selected(old('role_slug', $selectedRole) === $role->slug)>{{ $role->name }}</option>
            @endforeach
        </select>
        <div class="form-text">Branch Head and Branch Staff must be assigned to a branch.</div>
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
