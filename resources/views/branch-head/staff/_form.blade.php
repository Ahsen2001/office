@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Officer name</label>
        <input id="name" name="name" value="{{ old('name', $staff->name) }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $staff->email) }}" class="form-control @error('email') is-invalid @enderror" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="phone">Contact number</label>
        <input id="phone" name="phone" value="{{ old('phone', $staff->phone) }}" class="form-control @error('phone') is-invalid @enderror">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="designation">Designation</label>
        <input id="designation" name="designation" value="{{ old('designation', $staff->designation) }}" class="form-control @error('designation') is-invalid @enderror" placeholder="e.g. Accounts Officer" required>
        @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Role</label>
        <input value="Branch Staff" class="form-control" disabled>
        <input type="hidden" name="role" value="branch_staff">
        <div class="form-text">Branch Heads can create only Branch Staff accounts.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Branch</label>
        <input value="{{ $branch->name }}" class="form-control" disabled>
        <input type="hidden" name="branch_id" value="{{ $branch->id }}">
        <div class="form-text">The branch is fixed to your assigned branch.</div>
    </div>
    @unless($staff->exists)
        <div class="col-md-6">
            <label class="form-label" for="password">Password</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
        </div>
    @endunless
    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $staff->is_active))>
            <label class="form-check-label" for="is_active">Active status</label>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary"><i class="fa-regular fa-floppy-disk me-1"></i> Save Branch Staff</button>
    <a href="{{ route('branch-head.staff.index') }}" class="btn btn-light">Cancel</a>
</div>
