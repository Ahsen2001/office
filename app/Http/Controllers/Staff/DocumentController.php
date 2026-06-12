<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\DocumentType;
use App\Models\Person;
use App\Models\ServiceApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $documents = ApplicationDocument::with(['person', 'application', 'documentType', 'uploader'])
            ->when($search, function ($query) use ($search) {
                $query->where('document_title', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhereHas('person', fn ($personQuery) => $personQuery
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('person_code', 'like', "%{$search}%"))
                    ->orWhereHas('application', fn ($applicationQuery) => $applicationQuery
                        ->where('application_no', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('staff.documents.index', compact('documents', 'search'));
    }

    public function store(Request $request, ServiceApplication $application): RedirectResponse
    {
        $this->saveDocument($request, $application->person, $application);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function storeForPerson(Request $request, Person $person): RedirectResponse
    {
        $this->saveDocument($request, $person);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function preview(ApplicationDocument $document): BinaryFileResponse|StreamedResponse
    {
        [$disk, $path] = $this->storedFile($document);

        if (! in_array(strtolower((string) $document->file_type), ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            return Storage::disk($disk)->download($document->file_path, $document->file_name);
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="'.$document->file_name.'"',
        ]);
    }

    public function download(ApplicationDocument $document): StreamedResponse
    {
        [$disk] = $this->storedFile($document);

        return Storage::disk($disk)->download($document->file_path, $document->file_name);
    }

    public function destroy(ApplicationDocument $document): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    private function saveDocument(Request $request, Person $person, ?ServiceApplication $application = null): ApplicationDocument
    {
        $data = $request->validate([
            'document_type_id' => ['required', 'exists:document_types,id'],
            'document_title' => ['nullable', 'string', 'max:180'],
            'remarks' => ['nullable', 'string'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:'.config('office.uploads.max_kilobytes')],
        ]);

        $file = $request->file('document');
        $extension = strtolower($file->getClientOriginalExtension());
        $directory = $application
            ? 'documents/applications/'.$application->application_no
            : 'documents/people/'.$person->person_code;

        $path = $file->store($directory, 'local');
        $documentType = DocumentType::find($data['document_type_id']);

        return ApplicationDocument::create([
            'application_id' => $application?->id,
            'person_id' => $person->id,
            'document_type_id' => $data['document_type_id'],
            'document_title' => $data['document_title'] ?: $documentType?->name,
            'uploaded_by' => $request->user()->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $extension,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'remarks' => $data['remarks'] ?? null,
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
