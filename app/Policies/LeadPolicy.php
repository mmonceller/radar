<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function update(User $user, Lead $lead): bool
    {
        return $user->id === $lead->user_id;
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->id === $lead->user_id && $lead->awarded_at === null;
    }
}
