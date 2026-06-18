@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Person</label>
        <select name="person_id" class="form-select" required>
            <option value="">Select person</option>
            @foreach($people as $personOption)
                <option value="{{ $personOption->id }}" @selected(old('person_id', $application->person_id ?: $person?->id) == $personOption->id)>{{ $personOption->full_name }} - {{ $personOption->person_code }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Service</label>
        <select name="service_id" class="form-select" required>
            <option value="">Select service</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" @selected(old('service_id', $application->service_id) == $service->id)>{{ $service->name }} - {{ $service->branch?->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Branch</label>
        <select name="department_id" class="form-select">
            <option value="">Use service branch</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $application->branch_id ?: $application->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Assigned officer</label>
        <select name="assigned_officer_id" class="form-select">
            <option value="">Unassigned</option>
            @foreach($officers as $officer)
                <option value="{{ $officer->id }}" @selected(old('assigned_officer_id', $application->assigned_officer_id) == $officer->id)>{{ $officer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status_id" class="form-select" required>
            @foreach($statuses as $status)
                <option value="{{ $status->id }}" @selected(old('status_id', $application->status_id ?: $statuses->firstWhere('code', 'submitted')?->id) == $status->id)>{{ $status->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Submitted date</label>
        <input type="datetime-local" name="submitted_at" value="{{ old('submitted_at', optional($application->submitted_at)->format('Y-m-d\\TH:i')) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Deadline</label>
        <input type="date" name="due_date" value="{{ old('due_date', optional($application->due_date)->format('Y-m-d')) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select" required>
            @foreach(['low','normal','high','urgent'] as $priority)
                <option value="{{ $priority }}" @selected(old('priority', $application->priority ?: 'normal') === $priority)>{{ ucfirst($priority) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', $application->description) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $application->remarks) }}</textarea>
    </div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Save Application</button>
    <a href="{{ route('staff.applications.index') }}" class="btn btn-light">Cancel</a>
</div>
