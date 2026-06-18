@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Branch name</label><input name="name" value="{{ old('name', $branch->name) }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Branch code</label><input name="code" value="{{ old('code', $branch->code) }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Branch Head</label><select name="branch_head_user_id" class="form-select"><option value="">Not assigned</option>@foreach($heads as $head)<option value="{{ $head->id }}" @selected(old('branch_head_user_id', $branch->branch_head_user_id) == $head->id)>{{ $head->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Contact number</label><input name="phone" value="{{ old('phone', $branch->phone) }}" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $branch->email) }}" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">Location</label><input name="location" value="{{ old('location', $branch->location) }}" class="form-control"></div>
    <div class="col-12"><label class="form-label">Description</label><textarea name="description" rows="4" class="form-control">{{ old('description', $branch->description) }}</textarea></div>
    <div class="col-12"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $branch->is_active))><label class="form-check-label" for="is_active">Active</label></div></div>
</div>
<div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Save Branch</button><a href="{{ route('admin.branches.index') }}" class="btn btn-light">Cancel</a></div>
