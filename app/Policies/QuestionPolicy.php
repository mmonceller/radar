<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function update(User $user, Question $question): bool
    {
        return $user->id === $question->user_id && ! $question->leads()->exists();
    }

    public function followUp(User $user, Question $question): bool
    {
        return $user->id === $question->user_id && $question->leads()->exists();
    }

    public function award(User $user, Question $question): bool
    {
        return $user->id === $question->user_id;
    }
}
