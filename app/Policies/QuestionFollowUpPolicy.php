<?php

namespace App\Policies;

use App\Models\QuestionFollowUp;
use App\Models\User;

class QuestionFollowUpPolicy
{
    public function update(User $user, QuestionFollowUp $followUp): bool
    {
        return $user->id === $followUp->user_id
            && $user->id === $followUp->question->user_id;
    }

    public function delete(User $user, QuestionFollowUp $followUp): bool
    {
        return $this->update($user, $followUp);
    }
}
