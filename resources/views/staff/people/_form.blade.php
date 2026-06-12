@csrf

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="full_name">Full name</label>
        <input id="full_name" name="full_name" value="{{ old('full_name', $person->full_name) }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="gender">Gender</label>
        <select id="gender" name="gender" class="form-select" required>
            @foreach(['not_specified' => 'Not specified', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                <option value="{{ $value }}" @selected(old('gender', $person->gender) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="national_id">NIC number</label>
        <input id="national_id" name="national_id" value="{{ old('national_id', $person->national_id) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="passport_no">Passport number</label>
        <input id="passport_no" name="passport_no" value="{{ old('passport_no', $person->passport_no) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="date_of_birth">Date of birth</label>
        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($person->date_of_birth)->format('Y-m-d')) }}" class="form-control">
    </div>
    <div class="col-md-8">
        <label class="form-label" for="address_line_1">Address</label>
        <input id="address_line_1" name="address_line_1" value="{{ old('address_line_1', $person->address_line_1) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="city">City</label>
        <input id="city" name="city" value="{{ old('city', $person->city) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="phone">Contact number</label>
        <input id="phone" name="phone" value="{{ old('phone', $person->phone) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $person->email) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="occupation">Occupation</label>
        <input id="occupation" name="occupation" value="{{ old('occupation', $person->occupation) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="emergency_contact_name">Emergency contact name</label>
        <input id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $person->emergency_contact_name) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="emergency_contact_number">Emergency contact number</label>
        <input id="emergency_contact_number" name="emergency_contact_number" value="{{ old('emergency_contact_number', $person->emergency_contact_number) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="profile_photo">Profile photo</label>
        <input id="profile_photo" type="file" name="profile_photo" accept="image/*" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label" for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4" class="form-control">{{ old('notes', $person->notes) }}</textarea>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Save Person</button>
    <a href="{{ route('staff.people.index') }}" class="btn btn-light">Cancel</a>
</div>
