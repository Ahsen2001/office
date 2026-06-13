<?php

namespace App\Policies;

use App\Models\ApplicationDocument;
use App\Models\User;

class ApplicationDocumentPolicy
{
    public function view(User $user, ApplicationDocument $document): bool
    {
        if ($user->hasRole('admin', 'staff', 'manager')) {
            return true;
        }

        $document->loadMissing('application');

        return $user->hasRole('department_officer')
            && $user->department_id
            && $document->application
            && (int) $document->application->department_id === (int) $user->department_id;
    }

    public function delete(User $user, ApplicationDocument $document): bool
    {
        return $user->hasRole('admin');
    }
}
