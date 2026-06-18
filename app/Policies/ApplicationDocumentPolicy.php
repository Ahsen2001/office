<?php

namespace App\Policies;

use App\Models\ApplicationDocument;
use App\Models\User;

class ApplicationDocumentPolicy
{
    public function view(User $user, ApplicationDocument $document): bool
    {
        if ($user->hasRole('admin', 'management', 'reception')) {
            return true;
        }

        $document->loadMissing('application');

        return $user->hasRole('branch_head', 'branch_staff')
            && $user->branch_id
            && $document->application
            && (int) $document->application->branch_id === (int) $user->branch_id;
    }

    public function delete(User $user, ApplicationDocument $document): bool
    {
        return $user->hasRole('admin');
    }
}
