@extends('layouts.admin')

@section('title', 'Documents')
@section('page-title', 'Document Management')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <form method="GET" class="d-flex gap-2 flex-grow-1">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by document, person, person ID, or application no">
            <button class="btn btn-outline-primary">Search</button>
        </form>
    </div>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Document</th><th>Person</th><th>Application</th><th>Type</th><th>Uploaded By</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        <tr>
                            <td>
                                {{ $document->document_title ?? $document->file_name }}
                                <div class="text-muted small">{{ $document->file_name }} | {{ $document->file_size ? number_format($document->file_size / 1024, 1).' KB' : '-' }}</div>
                            </td>
                            <td>{{ $document->person?->full_name }}<div class="text-muted small">{{ $document->person?->person_code }}</div></td>
                            <td>{{ $document->application?->application_no ?? 'Person profile' }}</td>
                            <td>{{ $document->documentType?->name }}</td>
                            <td>{{ $document->uploader?->name }}</td>
                            <td class="text-end">
                                <a href="{{ route('staff.documents.preview', $document) }}" class="btn btn-sm btn-outline-secondary">Preview</a>
                                <a href="{{ route('staff.documents.download', $document) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                @if(auth()->user()?->hasRole('admin'))
                                    <form action="{{ route('staff.documents.destroy', $document) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No documents found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $documents->links() }}</div>
    </div>
@endsection
