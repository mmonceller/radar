<?php

namespace App\Livewire;

use App\Livewire\Leads\InteractsWithLeadForm;
use App\Models\Lead;
use App\Models\LeadVote;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowRequest extends Component
{
    use InteractsWithLeadForm;

    // Holds the core request post data context
    public Question $itemRequest;

    public bool $fromPosts = false;

    public $reportingLeadId = null;  // Holds the ID of the lead being flagged

    public $selectedOriginalId = ''; // Holds the target choice item selection

    // Mount runs once on initial HTTP page load
    public function mount(Question $request)
    {
        $this->fromPosts = request()->query('from') === 'posts';
        $this->itemRequest = $request->load(['category', 'user']);
    }

    public function submitLead()
    {
        abort_unless(Auth::check() && Auth::id() !== $this->itemRequest->user_id, 403);

        Lead::create(array_merge(
            [
                'question_id' => $this->itemRequest->id,
                'user_id' => Auth::id(),
            ],
            $this->validatedLeadAttributes(),
        ));

        $this->reset(['store_name', 'price', 'is_online', 'latitude', 'longitude', 'address', 'description', 'source_link']);
        session()->flash('lead_message', 'Your structured lead has been verified!');
        $this->dispatch('leads-updated');
    }

    public function vote($leadId, $type)
    {
        $userId = Auth::id();
        $lead = Lead::findOrFail($leadId);

        // SECURITY CHECK: Prevent users from voting on their own leads
        if ($lead->user_id === $userId) {
            session()->flash('lead_message', '❌ You cannot vote on your own lead submission.');

            return;
        }

        // Process the vote tracking logic
        $existingVote = LeadVote::where('lead_id', $leadId)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            if ($existingVote->type == $type) {
                $existingVote->delete(); // Retract vote if clicked again
            } else {
                $existingVote->update(['type' => $type]); // Switch from upvote to downvote or vice versa
            }
        } else {
            LeadVote::create([
                'lead_id' => $leadId,
                'user_id' => $userId,
                'type' => $type,
            ]);
        }

        // Recalculate and update the cached counters on the lead record
        $lead->update([
            'upvotes_count' => $lead->votes()->where('type', 1)->count(),
            'downvotes_count' => $lead->votes()->where('type', -1)->count(),
            'last_verified_at' => now(), // Treat a community vote as a fresh verification anchor
        ]);
    }

    // Triggers the tiny reporting modal selection block beneath a specific card
    public function toggleReportDuplicateForm($leadId)
    {
        if ($this->reportingLeadId === $leadId) {
            $this->reset(['reportingLeadId', 'selectedOriginalId']);
        } else {
            $this->reportingLeadId = $leadId;
            $this->selectedOriginalId = '';
        }
    }

    // Handles processing the duplicate binding action
    public function submitDuplicateReport()
    {
        $this->validate([
            'selectedOriginalId' => 'required|exists:leads,id',
            'reportingLeadId' => 'required|exists:leads,id',
        ], [
            'selectedOriginalId.required' => 'Please choose the original upvoted lead.',
        ]);

        $targetOriginal = Lead::findOrFail($this->selectedOriginalId);
        $duplicateLead = Lead::findOrFail($this->reportingLeadId);

        // Security Validation Checks
        if ($targetOriginal->id === $duplicateLead->id) {
            session()->flash('duplicate_error_'.$this->reportingLeadId, '❌ A lead cannot be a duplicate of itself.');

            return;
        }

        // Rule Validation Check: Must have a net-positive vote score balance
        $netScore = $targetOriginal->upvotes_count - $targetOriginal->downvotes_count;
        if ($netScore <= 0) {
            session()->flash('duplicate_error_'.$this->reportingLeadId, '❌ The chosen original lead must have positive upvotes.');

            return;
        }

        // Apply configuration change to DB
        $duplicateLead->update([
            'duplicate_of_id' => $targetOriginal->id,
        ]);

        $this->reset(['reportingLeadId', 'selectedOriginalId']);
        session()->flash('lead_message', 'Lead successfully tagged as a duplicate.');
    }

    public function render()
    {
        $leads = $this->itemRequest->leads()
            ->with(['user', 'votes', 'originalLead'])
            ->orderByRaw('(upvotes_count - downvotes_count) DESC')
            ->latest()
            ->get();

        return view('livewire.show-request', [
            'leads' => $leads,
            'awardedLead' => $leads->first(fn (Lead $lead) => $lead->isAwarded()),
        ])->layout('layouts.app');
    }
}
