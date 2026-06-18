<?php

namespace App\Policies;

use App\Models\ApplicationDocument;
use App\Models\User;

class ApplicationDocumentPolicy
{
    public function view(User $user, ApplicationDocument $document): bool
    {
        if ($user->hasRole('admin', 'management')) {
            return true;
        }

        $document->loadMissing('application');

        if ($user->hasRole('reception')) {
            return $document->visibility !== 'branch';
        }

        return $user->hasRole('branch_head', 'branch_staff')
            && $user->branch_id
            && (int) $document->branch_id === (int) $user->branch_id
            && in_array($document->visibility, ['internal', 'branch', 'public'], true);
    }

    public function delete(User $user, ApplicationDocument $document): bool
    {
        return $user->hasRole('admin');
    }
}
