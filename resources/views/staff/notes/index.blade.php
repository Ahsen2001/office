@extends('layouts.admin')

@section('title', 'Notes')
@section('page-title', 'Notes and Remarks')

@section('content')
    <form method="GET" class="d-flex gap-2 mb-4">
        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search notes, people, applications, or note type">
        <button class="btn btn-outline-primary">Search</button>
    </form>

    <div class="card soft-card"><div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Note</th><th>Person</th><th>Application</th><th>Type</th><th>Visibility</th><th>By</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($notes as $note)
                    <tr>
                        <td>{{ Str::limit($note->note, 90) }}</td>
                        <td>{{ $note->person?->full_name }}</td>
                        <td>{{ $note->application?->application_no ?? '-' }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $note->note_type)) }}</td>
                        <td><span class="badge text-bg-secondary">{{ ucwords(str_replace('_', ' ', $note->visibility)) }}</span></td>
                        <td>{{ $note->creator?->name }}</td>
                        <td class="text-end">
                            @if(auth()->id() === (int) $note->created_by)
                                <a href="{{ route('staff.notes.edit', $note) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @endif
                            @if(auth()->user()?->hasRole('admin'))
                                <form action="{{ route('staff.notes.destroy', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this note?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No notes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div><div class="card-body border-top">{{ $notes->links() }}</div></div>
@endsection
