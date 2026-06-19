@csrf
@php($selectedBranchId = old('branch_id', $application?->branch_id ?? $appointment->branch_id))
@php($selectedOfficerId = old('officer_id', $appointment->officer_id))

<div
    class="row g-3"
    data-appointment-form
    data-number-url="{{ route('staff.branches.appointment-number', ['branch' => '__BRANCH__']) }}"
    data-officers-url="{{ route('staff.branches.officers', ['branch' => '__BRANCH__']) }}"
    data-editing="{{ $appointment->exists ? '1' : '0' }}"
>
    <div class="col-lg-6">
        <label class="form-label" for="person_id">Person</label>
        <select id="person_id" name="person_id" class="form-select @error('person_id') is-invalid @enderror" required>
            <option value="">Select person</option>
            @foreach($people as $item)
                <option value="{{ $item->id }}" @selected(old('person_id', $person?->id ?? $appointment->person_id) == $item->id)>
                    {{ $item->full_name }} - {{ $item->person_code }}
                </option>
            @endforeach
        </select>
        @error('person_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label" for="application_id">Related application</label>
        <select id="application_id" name="application_id" class="form-select @error('application_id') is-invalid @enderror">
            <option value="">No related application</option>
            @foreach($applications as $item)
                <option
                    value="{{ $item->id }}"
                    data-person-id="{{ $item->person_id }}"
                    data-branch-id="{{ $item->branch_id }}"
                    @selected(old('application_id', $application?->id ?? $appointment->application_id) == $item->id)
                >
                    {{ $item->application_no }} - {{ $item->person?->full_name }} - {{ $item->branch?->name }}
                </option>
            @endforeach
        </select>
        @error('application_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Selecting an application automatically matches its person and branch.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="branch_id">Branch</label>
        <select id="branch_id" name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
            <option value="">Select branch</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected($selectedBranchId == $branch->id)>
                    {{ $branch->name }} ({{ $branch->code }})
                </option>
            @endforeach
        </select>
        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="appointment_number">Appointment number</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
            <input id="appointment_number" value="{{ $appointmentNumber }}" class="form-control" readonly placeholder="Select a branch to generate">
        </div>
        <div class="form-text">The final number is reserved when the appointment is saved.</div>
    </div>

    <div class="col-12">
        <label class="form-label" for="officer_id">Assigned officer</label>
        <select id="officer_id" name="officer_id" class="form-select @error('officer_id') is-invalid @enderror" data-selected-officer="{{ $selectedOfficerId }}">
            <option value="">Unassigned</option>
            @foreach($officers as $officer)
                @php($designation = $officer->designation ?: $officer->roles->first()?->name ?: 'Officer')
                <option value="{{ $officer->id }}" @selected($selectedOfficerId == $officer->id)>
                    {{ $officer->name }} - {{ $designation }}
                </option>
            @endforeach
        </select>
        @error('officer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text" data-officer-help>Select a branch to load its active Branch Head and Branch Staff.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="appointment_date">Appointment date</label>
        <input id="appointment_date" type="date" name="appointment_date" min="{{ today()->format('Y-m-d') }}" value="{{ old('appointment_date', $appointment->appointment_date?->format('Y-m-d')) }}" class="form-control @error('appointment_date') is-invalid @enderror" required>
        @error('appointment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="start_time">Appointment time</label>
        <input id="start_time" type="time" name="start_time" value="{{ old('start_time', $appointment->start_time?->format('H:i')) }}" class="form-control @error('start_time') is-invalid @enderror" required>
        @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="end_time">End time</label>
        <input id="end_time" type="time" name="end_time" value="{{ old('end_time', $appointment->end_time?->format('H:i')) }}" class="form-control @error('end_time') is-invalid @enderror">
        @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="status">Appointment status</label>
        <select id="status" name="status" class="form-select">
            @foreach(['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'missed' => 'Missed', 'rescheduled' => 'Rescheduled'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $appointment->status ?: 'scheduled') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-8">
        <label class="form-label" for="purpose">Purpose</label>
        <input id="purpose" list="appointment-purposes" name="purpose" value="{{ old('purpose', $appointment->purpose) }}" class="form-control @error('purpose') is-invalid @enderror" placeholder="Select or enter a visit purpose" required>
        <datalist id="appointment-purposes">
            <option value="Document verification">
            <option value="Submit missing documents">
            <option value="Payment verification">
            <option value="Meet assigned officer">
            <option value="Application follow-up">
            <option value="Certificate collection">
            <option value="Approval process">
        </datalist>
        @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="remarks">Remarks</label>
        <textarea id="remarks" name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $appointment->remarks) }}</textarea>
        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary"><i class="fa-regular fa-calendar-check me-1"></i> Save Appointment</button>
        <a href="{{ route('staff.appointments.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const form = document.querySelector('[data-appointment-form]');
        if (!form) return;

        const branch = document.getElementById('branch_id');
        const officer = document.getElementById('officer_id');
        const number = document.getElementById('appointment_number');
        const application = document.getElementById('application_id');
        const person = document.getElementById('person_id');
        const help = form.querySelector('[data-officer-help]');
        const editing = form.dataset.editing === '1';

        const endpoint = (template, branchId) => template.replace('__BRANCH__', branchId);

        async function loadBranchData() {
            const branchId = branch.value;
            const previousOfficer = officer.dataset.selectedOfficer || officer.value;
            officer.replaceChildren(new Option('Unassigned', ''));

            if (!branchId) {
                if (!editing) number.value = '';
                help.textContent = 'Select a branch to load its active Branch Head and Branch Staff.';
                return;
            }

            number.value = editing ? number.value : 'Generating...';
            officer.disabled = true;
            help.textContent = 'Loading branch officers...';

            try {
                const [numberResponse, officersResponse] = await Promise.all([
                    fetch(endpoint(form.dataset.numberUrl, branchId), { headers: { Accept: 'application/json' } }),
                    fetch(endpoint(form.dataset.officersUrl, branchId), { headers: { Accept: 'application/json' } }),
                ]);

                if (!numberResponse.ok || !officersResponse.ok) throw new Error('Unable to load branch appointment data.');

                const numberData = await numberResponse.json();
                const officersData = await officersResponse.json();

                if (!editing) number.value = numberData.appointment_number;

                officersData.officers.forEach((item) => {
                    officer.add(new Option(item.label, item.id));
                });

                if (officersData.officers.length === 1) {
                    officer.value = String(officersData.officers[0].id);
                    help.textContent = 'The only available officer was selected automatically.';
                } else if (officersData.officers.length > 1) {
                    officer.value = officersData.officers.some((item) => String(item.id) === String(previousOfficer))
                        ? String(previousOfficer)
                        : '';
                    help.textContent = `${officersData.officers.length} active officers are available in this branch.`;
                } else {
                    help.textContent = 'No active Branch Head or Branch Staff found. The appointment will remain unassigned.';
                }
            } catch (error) {
                if (!editing) number.value = 'Unable to generate';
                help.textContent = 'Branch appointment data could not be loaded. Please try again.';
            } finally {
                officer.disabled = false;
            }
        }

        application.addEventListener('change', () => {
            const option = application.selectedOptions[0];
            if (!option?.value) return;

            person.value = option.dataset.personId || '';
            branch.value = option.dataset.branchId || '';
            loadBranchData();
        });

        branch.addEventListener('change', loadBranchData);

        if (branch.value) loadBranchData();
    })();
</script>
@endpush
