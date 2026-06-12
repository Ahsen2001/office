<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\ServiceApplication;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function store(Request $request, ServiceApplication $application)
    {
        $data = $request->validate([
            'document_type_id' => ['required', 'exists:document_types,id'],
            'document' => ['required', 'file', 'max:'.config('office.uploads.max_kilobytes')],
        ]);

        $file = $request->file('document');
        $path = $file->store('documents/'.$application->application_no, 'public');

        return ApplicationDocument::create([
            'application_id' => $application->id,
            'person_id' => $application->person_id,
            'document_type_id' => $data['document_type_id'],
            'uploaded_by' => $request->user()->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }
}
