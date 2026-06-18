<?php

namespace App\Http\Controllers;

use App\Models\ApplicationNote;
use App\Models\Person;
use App\Models\ServiceApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $notes = ApplicationNote::with(['person', 'application', 'creator'])
            ->when($search, fn ($query) => $query
                ->where('note', 'like', "%{$search}%")
                ->orWhere('note_type', 'like', "%{$search}%")
                ->orWhereHas('person', fn ($personQuery) => $personQuery->where('full_name', 'like', "%{$search}%"))
                ->orWhereHas('application', fn ($applicationQuery) => $applicationQuery->where('application_no', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('staff.notes.index', compact('notes', 'search'));
    }

    public function storeForPerson(Request $request, Person $person): RedirectResponse
    {
        $data = $this->validated($request);

        ApplicationNote::create($data + [
            'person_id' => $person->id,
            'application_id' => null,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    public function storeForApplication(Request $request, ServiceApplication $application): RedirectResponse
    {
        $this->authorizeDepartmentOfficer($request, $application);

        $data = $this->validated($request);

        ApplicationNote::create($data + [
            'person_id' => $application->person_id,
            'application_id' => $application->id,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    public function edit(ApplicationNote $note): View
    {
        abort_unless(auth()->id() === (int) $note->created_by, 403);

        return view('staff.notes.edit', compact('note'));
    }

    public function update(Request $request, ApplicationNote $note): RedirectResponse
    {
        abort_unless($request->user()->id === (int) $note->created_by, 403);

        $note->update($this->validated($request));

        return redirect()->route('staff.notes.index')->with('success', 'Note updated successfully.');
    }

    public function destroy(ApplicationNote $note): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $note->delete();

        return back()->with('success', 'Note deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'note_type' => ['required', 'string', 'max:60'],
            'visibility' => ['required', 'in:internal,department,public'],
            'note' => ['required', 'string'],
        ]);
    }

    private function authorizeDepartmentOfficer(Request $request, ServiceApplication $application): void
    {
        if (! $request->user()?->hasRole('branch_head', 'branch_staff')) {
            return;
        }

        abort_unless((int) $request->user()->branch_id === (int) $application->branch_id, 403);
    }
}
