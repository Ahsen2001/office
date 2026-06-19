<?php

namespace App\Http\Controllers;

use App\Models\OfficeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = OfficeNotification::query()
            ->with(['person', 'application'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, OfficeNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        OfficeNotification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
