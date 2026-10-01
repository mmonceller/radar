<?php

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\QuestionFollowUp;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class FollowUps extends Component
{
    public Question $question;

    public bool $fromPosts = false;

    public $body = '';

    public $editingId = null;

    public $editingBody = '';

    #[On('leads-updated')]
    public function refreshLock(): void
    {
        $this->question->refresh();
        $this->question->unsetRelation('leads');
    }

    public function submit(): void
    {
        abort_unless(Auth::user()?->can('followUp', $this->question), 403);

        $this->validate([
            'body' => 'required|min:3|max:1000',
        ]);

        $this->question->followUps()->create([
            'user_id' => Auth::id(),
            'body' => trim($this->body),
        ]);

        $this->reset('body');
        session()->flash('follow_up_saved', 'Your specification update is now visible on this request.');
    }

    public function edit(int $followUpId): void
    {
        $followUp = $this->ownedFollowUp($followUpId);
        $this->editingId = $followUp->id;
        $this->editingBody = $followUp->body;
    }

    public function updateFollowUp(): void
    {
        $followUp = $this->ownedFollowUp((int) $this->editingId);

        $this->validate([
            'editingBody' => 'required|min:3|max:1000',
        ]);

        $followUp->update([
            'body' => trim($this->editingBody),
        ]);

        $this->reset('editingId', 'editingBody');
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editingBody');
    }

    public function deleteFollowUp(int $followUpId): void
    {
        $this->ownedFollowUp($followUpId)->delete();

        if ((int) $this->editingId === $followUpId) {
            $this->reset('editingId', 'editingBody');
        }
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.questions.follow-ups', [
            'followUps' => $this->question->followUps()->with('user')->latest()->get(),
            'canEdit' => $user?->can('update', $this->question) ?? false,
            'canFollowUp' => $user?->can('followUp', $this->question) ?? false,
        ]);
    }

    protected function ownedFollowUp(int $followUpId): QuestionFollowUp
    {
        $followUp = $this->question->followUps()->whereKey($followUpId)->firstOrFail();
        $followUp->setRelation('question', $this->question);

        abort_unless(Auth::user()?->can('update', $followUp), 403);

        return $followUp;
    }
}
