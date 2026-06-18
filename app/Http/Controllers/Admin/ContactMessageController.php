<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();

        $messages = ContactMessage::query()
            ->with('reader')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            }))
            ->when($status, fn ($query) => $query->where('status', '=', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.contact-messages.index', compact('messages', 'search', 'status'));
    }

    public function show(Request $request, ContactMessage $contactMessage): View
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update([
                'status' => 'read',
                'read_at' => now(),
                'read_by' => $request->user()->id,
            ]);
        }

        return view('admin.contact-messages.show', [
            'contactMessage' => $contactMessage->load('reader'),
        ]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,read,replied,closed'],
        ]);

        $oldStatus = $contactMessage->status;
        $contactMessage->update([
            'status' => $data['status'],
            'read_at' => $contactMessage->read_at ?? now(),
            'read_by' => $contactMessage->read_by ?? $request->user()->id,
        ]);

        AuditLogger::log(
            'status_update',
            'contact_messages',
            "Changed contact message #{$contactMessage->id} to {$data['status']}.",
            $contactMessage,
            ['status' => $oldStatus],
            ['status' => $data['status']],
            $request
        );

        return back()->with('success', 'Message status updated.');
    }
}
