@extends('layouts.admin')

@section('title', 'People')
@section('page-title', 'People Registration')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <form method="GET" class="d-flex gap-2">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search name, NIC, phone, person ID">
            <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-search"></i></button>
        </form>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.scanner.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-camera me-1"></i> Scanner</a>
            <a href="{{ route('staff.people.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Add Person</a>
        </div>
    </div>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Person</th>
                        <th>ID</th>
                        <th>Contact</th>
                        <th>City</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($people as $person)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $person->full_name }}</div>
                                <div class="text-muted small">{{ $person->national_id ?: $person->passport_no }}</div>
                            </td>
                            <td>{{ $person->person_code }}</td>
                            <td>
                                <div>{{ $person->phone }}</div>
                                <div class="text-muted small">{{ $person->email }}</div>
                            </td>
                            <td>{{ $person->city }}</td>
                            <td>{{ $person->registered_at?->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('staff.people.show', $person) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="{{ route('staff.people.edit', $person) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <a href="{{ route('staff.people.card', $person) }}" class="btn btn-sm btn-outline-success">Card</a>
                                @if(auth()->user()->hasRole('admin'))
                                    <form method="POST" action="{{ route('admin.people.destroy', $person) }}" class="d-inline" onsubmit="return confirm('Delete this person?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No people found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $people->links() }}</div>
    </div>
@endsection
