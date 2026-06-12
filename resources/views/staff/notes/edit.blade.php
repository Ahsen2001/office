@extends('layouts.admin')

@section('title', 'Edit Note')
@section('page-title', 'Edit Note')

@section('content')
    <div class="card soft-card"><div class="card-body">
        <form method="POST" action="{{ route('staff.notes.update', $note) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Note Type</label>
                    <input type="text" name="note_type" value="{{ old('note_type', $note->note_type) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Visibility</label>
                    <select name="visibility" class="form-select" required>
                        <option value="internal" @selected(old('visibility', $note->visibility) === 'internal')>Internal only</option>
                        <option value="department" @selected(old('visibility', $note->visibility) === 'department')>Department only</option>
                        <option value="public" @selected(old('visibility', $note->visibility) === 'public')>Public visible</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Note Message</label>
                    <textarea name="note" rows="5" class="form-control" required>{{ old('note', $note->note) }}</textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">Update Note</button>
                    <a href="{{ route('staff.notes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div></div>
@endsection
