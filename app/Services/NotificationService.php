<?php

namespace App\Services;

use App\Models\OfficeNotification;
use App\Models\ServiceApplication;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function forUser(?User $user, string $title, string $message, string $type = 'system', ?ServiceApplication $application = null): ?OfficeNotification
    {
        if (! $user) {
            return null;
        }

        return OfficeNotification::create([
            'user_id' => $user->id,
            'person_id' => $application?->person_id,
            'application_id' => $application?->id,
            'channel' => 'system',
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'sent_at' => now(),
        ]);
    }

    public function managers(string $title, string $message, string $type = 'system', ?ServiceApplication $application = null): Collection
    {
        return User::where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'management'))
            ->get()
            ->map(fn (User $user) => $this->forUser($user, $title, $message, $type, $application))
            ->filter()
            ->values();
    }

    public function assignedOfficer(ServiceApplication $application, string $title, string $message, string $type = 'application'): ?OfficeNotification
    {
        return $this->forUser($application->assignedOfficer, $title, $message, $type, $application);
    }
}
