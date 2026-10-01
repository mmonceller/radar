<?php

namespace App\Livewire\Leads;

use App\Leads\PurchaseAward;
use App\Models\Lead;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class AwardLead extends Component
{
    use WithFileUploads;

    public Lead $lead;

    public $verifiedPrice = '';

    public $verificationImage;

    public $showForm = false;

    #[On('lead-awarded')]
    public function syncAward(): void
    {
        $this->lead->refresh();
        $this->showForm = false;
    }

    public function openForm(): void
    {
        $this->ensureOwner();

        if ($this->lead->isAwarded() || $this->anotherLeadIsAwarded()) {
            return;
        }

        $this->showForm = true;
    }

    public function cancel(): void
    {
        $this->reset('showForm', 'verifiedPrice', 'verificationImage');
        $this->resetValidation();
    }

    public function submit(): void
    {
        $this->ensureOwner();

        $validated = $this->validate([
            'verifiedPrice' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'verificationImage' => ['required', 'image', 'max:4096'],
        ]);

        PurchaseAward::grant(
            Auth::user(),
            $this->lead,
            $this->verificationImage,
            $validated['verifiedPrice'],
        );

        $this->lead->refresh();
        $this->reset('showForm', 'verifiedPrice', 'verificationImage');
        $this->dispatch('lead-awarded');
    }

    public function revoke(): void
    {
        $this->ensureOwner();

        PurchaseAward::revoke(Auth::user(), $this->lead);

        $this->lead->refresh();
        $this->dispatch('lead-awarded');
    }

    public function render()
    {
        $ownerId = Question::query()->whereKey($this->lead->question_id)->value('user_id');

        return view('livewire.leads.award-lead', [
            'isOwner' => Auth::id() !== null && Auth::id() === $ownerId,
            'anotherAwarded' => $this->anotherLeadIsAwarded(),
        ]);
    }

    protected function ensureOwner(): void
    {
        $question = Question::query()->findOrFail($this->lead->question_id);

        abort_unless(Auth::user()?->can('award', $question), 403);
    }

    protected function anotherLeadIsAwarded(): bool
    {
        return Lead::query()
            ->where('question_id', $this->lead->question_id)
            ->whereNotNull('awarded_at')
            ->whereKeyNot($this->lead->id)
            ->exists();
    }
}
