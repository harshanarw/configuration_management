<?php

namespace App\Policies;

use App\Models\{User, ChangeRequest};

class ChangeRequestPolicy
{
    public function review(User $user, ChangeRequest $changeRequest): bool
    {
        return in_array($user->role, ['Reviewer', 'Admin'])
            && $changeRequest->status === 'pending'
            && !$changeRequest->reviewer_approved
            && !$changeRequest->reviewer_rejected;
    }

    public function approve(User $user, ChangeRequest $changeRequest): bool
    {
        return in_array($user->role, ['Approver', 'Admin'])
            && $changeRequest->reviewer_approved
            && !$changeRequest->approver_approved
            && !$changeRequest->approver_rejected;
    }
}
