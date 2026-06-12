@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Person</label>
        <select name="person_id" class="form-select" @if($application) disabled @endif>
            <option value="">Select person</option>
            @foreach($people as $item)
                <option value="{{ $item->id }}" @selected(old('person_id', $person?->id ?? $appointment->person_id) == $item->id)>{{ $item->full_name }} - {{ $item->person_code }}</option>
            @endforeach
        </select>
        @if($application)<input type="hidden" name="person_id" value="{{ $application->person_id }}">@endif
    </div>
    <div class="col-md-6">
        <label class="form-label">Application</label>
        <select name="application_id" class="form-select">
            <option value="">Person profile appointment</option>
            @foreach($applications as $item)
                <option value="{{ $item->id }}" @selected(old('application_id', $application?->id ?? $appointment->application_id) == $item->id)>{{ $item->application_no }} - {{ $item->person?->full_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select" @if($application) disabled @endif>
            <option value="">Select department</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $application?->department_id ?? $appointment->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        @if($application)<input type="hidden" name="department_id" value="{{ $application->department_id }}">@endif
    </div>
    <div class="col-md-6">
        <label class="form-label">Assigned Officer</label>
        <select name="officer_id" class="form-select">
            <option value="">Unassigned</option>
            @foreach($officers as $officer)
                <option value="{{ $officer->id }}" @selected(old('officer_id', $appointment->officer_id) == $officer->id)>{{ $officer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Appointment Date</label>
        <input type="date" name="appointment_date" value="{{ old('appointment_date', $appointment->appointment_date?->format('Y-m-d')) }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Appointment Time</label>
        <input type="time" name="start_time" value="{{ old('start_time', $appointment->start_time ? $appointment->start_time->format('H:i') : '') }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">End Time</label>
        <input type="time" name="end_time" value="{{ old('end_time', $appointment->end_time ? $appointment->end_time->format('H:i') : '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Appointment Status</label>
        <select name="status" class="form-select">
            @foreach(['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'missed' => 'Missed', 'rescheduled' => 'Rescheduled'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $appointment->status ?: 'scheduled') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Purpose</label>
        <input type="text" name="purpose" value="{{ old('purpose', $appointment->purpose) }}" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" rows="3" class="form-control">{{ old('remarks', $appointment->remarks) }}</textarea>
    </div>
    <div class="col-12">
        <button class="btn btn-primary">Save Appointment</button>
        <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</div>
