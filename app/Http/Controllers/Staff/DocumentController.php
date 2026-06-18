<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\DocumentType;
use App\Models\Person;
use App\Models\ServiceApplication;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $documents = ApplicationDocument::with(['person', 'application', 'documentType', 'uploader'])
            ->when($request->user()->isBranchRestricted(), fn ($query) => $query->where('branch_id', $request->user()->branch_id))
            ->when($request->user()->hasRole('reception'), fn ($query) => $query->where('visibility', '!=', 'branch'))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('document_title', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%")
                        ->orWhereHas('person', fn ($personQuery) => $personQuery
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('person_code', 'like', "%{$search}%"))
                        ->orWhereHas('application', fn ($applicationQuery) => $applicationQuery
                            ->where('application_no', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('staff.documents.index', compact('documents', 'search'));
    }

    public function store(Request $request, ServiceApplication $application): RedirectResponse
    {
        $document = $this->saveDocument($request, $application->person, $application);
        AuditLogger::log('upload', 'documents', "Uploaded document {$document->file_name}.", $document, null, $document->only(['application_id', 'person_id', 'file_name', 'file_type']), $request);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function storeForPerson(Request $request, Person $person): RedirectResponse
    {
        $document = $this->saveDocument($request, $person);
        AuditLogger::log('upload', 'documents', "Uploaded document {$document->file_name}.", $document, null, $document->only(['application_id', 'person_id', 'file_name', 'file_type']), $request);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function preview(ApplicationDocument $document)
    {
        Gate::authorize('view', $document);

        [$disk, $path] = $this->storedFile($document);

        if (! in_array(strtolower((string) $document->file_type), ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            // use absolute path and response()->download to avoid relying on dynamic Storage methods
            return response()->download($path, $document->file_name);
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="'.$document->file_name.'"',
        ]);
    }

    public function download(ApplicationDocument $document)
    {
        Gate::authorize('view', $document);

        [$disk] = $this->storedFile($document);

        $stream = Storage::disk($disk)->readStream($document->file_path);

        if ($stream === false) {
            abort(404, 'Document file not found.');
        }

        return response()->streamDownload(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, $document->file_name, ['Content-Type' => $document->mime_type ?: 'application/octet-stream']);
    }

    public function destroy(ApplicationDocument $document): RedirectResponse
    {
        Gate::authorize('delete', $document);

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $fileName = $document->file_name;
        $document->delete();
        AuditLogger::log('delete', 'documents', "Deleted document {$fileName}.", $document, ['file_name' => $fileName], null, request());

        return back()->with('success', 'Document deleted successfully.');
    }

    private function saveDocument(Request $request, Person $person, ?ServiceApplication $application = null): ApplicationDocument
    {
        $data = $request->validate([
            'document_type_id' => ['required', 'exists:document_types,id'],
            'document_title' => ['nullable', 'string', 'max:180'],
            'remarks' => ['nullable', 'string'],
            'visibility' => ['required', 'in:internal,branch,public'],
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'mimetypes:application/pdf,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'max:'.config('office.uploads.max_kilobytes'),
            ],
        ]);

        $file = $request->file('document');
        if (! $file) {
            abort(422, 'No document file uploaded.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $directory = $application !== null
            ? "documents/applications/{$application->application_no}"
            : "documents/people/{$person->person_code}";

        $path = $file->store($directory, 'local');
        $documentType = DocumentType::query()
            ->whereKey($data['document_type_id'])
            ->first();

        return ApplicationDocument::create([
            'application_id' => $application?->id,
            'person_id' => $person->id,
            'branch_id' => $application?->branch_id ?: $request->user()?->branch_id,
            'document_type_id' => $data['document_type_id'],
            'document_title' => $data['document_title'] ?: $documentType?->name,
            'uploaded_by' => $request->user()?->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $extension,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'remarks' => $data['remarks'] ?? null,
            'visibility' => $data['visibility'],
        ]);
    }

    private function storedFile(ApplicationDocument $document): array
    {
        if (Storage::disk('local')->exists($document->file_path)) {
            return ['local', Storage::disk('local')->path($document->file_path)];
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            return ['public', Storage::disk('public')->path($document->file_path)];
        }

        abort(404, 'Document file not found.');
    }
}
