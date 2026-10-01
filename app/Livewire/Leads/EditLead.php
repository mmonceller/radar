<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditLead extends Component
{
    use InteractsWithLeadForm;

    public Lead $lead;

    public function mount(Lead $lead): void
    {
        abort_unless(Auth::user()?->can('update', $lead), 403);

        $this->lead = $lead;
        $this->fillLeadFields($lead);
    }

    public function save()
    {
        abort_unless(Auth::user()?->can('update', $this->lead), 403);

        $this->lead->update($this->validatedLeadAttributes());

        session()->flash('lead_updated', 'Your lead was updated.');

        return $this->redirect(route('requests.show', $this->lead->question_id), navigate: true);
    }

    public function delete()
    {
        abort_unless($this->lead->user_id === Auth::id(), 403);

        if ($this->lead->awarded_at) {
            $this->addError('delete', 'This lead has a verified purchase. The requester has to remove that award before the lead can be deleted.');

            return;
        }

        $this->lead->delete();

        session()->flash('manage_message', 'Lead deleted.');

        return $this->redirect(route('posts.manage'), navigate: true);
    }

    public function render()
    {
        return view('livewire.leads.edit-lead', [
            'question' => $this->lead->question()->first(),
        ])->layout('layouts.app');
    }
}
