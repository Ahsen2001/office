@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Service name</label>
        <input id="name" name="name" value="{{ old('name', $service->name) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="code">Service code</label>
        <input id="code" name="code" value="{{ old('code', $service->code) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="department_id">Department</label>
        <select id="department_id" name="department_id" class="form-select" required>
            <option value="">Select department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $service->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="fee_amount">Service fee</label>
        <input id="fee_amount" type="number" step="0.01" min="0" name="fee_amount" value="{{ old('fee_amount', $service->fee_amount ?? 0) }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="processing_time_days">Processing time</label>
        <input id="processing_time_days" type="number" min="0" name="processing_time_days" value="{{ old('processing_time_days', $service->processing_time_days ?? $service->estimated_days) }}" class="form-control" placeholder="Days">
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $service->description) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="required_documents">Required documents</label>
        <textarea id="required_documents" name="required_documents" rows="4" class="form-control" placeholder="One document per line">{{ old('required_documents', implode(PHP_EOL, $requiredDocuments)) }}</textarea>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $service->is_active))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Save Service</button>
    <a href="{{ route('admin.services.index') }}" class="btn btn-light">Cancel</a>
</div>
