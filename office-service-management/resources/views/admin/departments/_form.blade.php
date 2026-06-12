@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Department name</label>
        <input id="name" name="name" value="{{ old('name', $department->name) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="code">Department code</label>
        <input id="code" name="code" value="{{ old('code', $department->code) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="phone">Contact number</label>
        <input id="phone" name="phone" value="{{ old('phone', $department->phone) }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $department->email) }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="location">Location</label>
        <input id="location" name="location" value="{{ old('location', $department->location) }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="department_officer_id">Department officer</label>
        <select id="department_officer_id" name="department_officer_id" class="form-select">
            <option value="">Not assigned</option>
            @foreach ($officers as $officer)
                <option value="{{ $officer->id }}" @selected(old('department_officer_id', $department->department_officer_id) == $officer->id)>{{ $officer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" rows="4" class="form-control">{{ old('description', $department->description) }}</textarea>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $department->is_active))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Save Department</button>
    <a href="{{ route('admin.departments.index') }}" class="btn btn-light">Cancel</a>
</div>
