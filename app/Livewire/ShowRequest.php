<?php

namespace App\Livewire;

use App\Leads\DownvoteNotes;
use App\Leads\DownvoteReason;
use App\Livewire\Leads\InteractsWithLeadForm;
use App\Models\Lead;
use App\Models\LeadVote;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ShowRequest extends Component
{
    use InteractsWithLeadForm;

    // Holds the core request post data context
    public Question $itemRequest;

    public bool $fromPosts = false;

    public $reportingLeadId = null;  // Holds the ID of the lead being flagged

    public $selectedOriginalId = ''; // Holds the target choice item selection

    public $downvotingLeadId = null;

    public $downvoteReason = '';

    public $downvoteExplanation = '';

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
        if (! Auth::check()) {
            session()->flash('lead_message', 'Log in to vote on a lead.');

            return;
        }

        $lead = $this->leadOnThisRequest((int) $leadId);

        if ($lead->user_id === Auth::id()) {
            session()->flash('lead_message', 'You cannot vote on your own lead.');

            return;
        }

        $existingVote = $this->existingVote($lead);

        if ((int) $type === -1) {
            if ($existingVote && (int) $existingVote->type === -1) {
                $existingVote->delete();
                $this->refreshLeadCounts($lead);
            }

            return;
        }

        if ((int) $type !== 1) {
            return;
        }

        if ($existingVote) {
            if ((int) $existingVote->type === 1) {
                $existingVote->delete();
            } else {
                $existingVote->update([
                    'type' => 1,
                    'reason' => null,
                    'explanation' => null,
                ]);
            }
        } else {
            LeadVote::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'type' => 1,
            ]);
        }

        if ((int) $this->downvotingLeadId === $lead->id) {
            $this->reset(['downvotingLeadId', 'downvoteReason', 'downvoteExplanation']);
        }

        $this->refreshLeadCounts($lead);
    }

    public function startDownvote(int $leadId): void
    {
        if (! Auth::check()) {
            session()->flash('lead_message', 'Log in to vote on a lead.');

            return;
        }

        $lead = $this->leadOnThisRequest($leadId);

        if ($lead->user_id === Auth::id()) {
            session()->flash('lead_message', 'You cannot vote on your own lead.');

            return;
        }

        $existingVote = $this->existingVote($lead);

        if ($existingVote && (int) $existingVote->type === -1) {
            $existingVote->delete();
            $this->refreshLeadCounts($lead);
            $this->reset(['downvotingLeadId', 'downvoteReason', 'downvoteExplanation']);

            return;
        }

        if ((int) $this->downvotingLeadId === $lead->id) {
            $this->reset(['downvotingLeadId', 'downvoteReason', 'downvoteExplanation']);

            return;
        }

        $this->downvotingLeadId = $lead->id;
        $this->downvoteReason = '';
        $this->downvoteExplanation = '';
        $this->resetValidation();
    }

    public function cancelDownvote(): void
    {
        $this->reset(['downvotingLeadId', 'downvoteReason', 'downvoteExplanation']);
        $this->resetValidation();
    }

    public function submitDownvote(): void
    {
        abort_unless(Auth::check(), 403);

        $this->validate([
            'downvotingLeadId' => 'required|integer',
            'downvoteReason' => ['required', Rule::in(DownvoteReason::values())],
            'downvoteExplanation' => 'nullable|max:280',
        ], [
            'downvoteReason.required' => 'Choose what was wrong with this lead.',
        ]);

        $lead = $this->leadOnThisRequest((int) $this->downvotingLeadId);

        abort_unless($lead->user_id !== Auth::id(), 403);

        $explanation = trim((string) $this->downvoteExplanation);
        $attributes = [
            'type' => -1,
            'reason' => $this->downvoteReason,
            'explanation' => $explanation === '' ? null : $explanation,
        ];

        $existingVote = $this->existingVote($lead);

        if ($existingVote) {
            $existingVote->update($attributes);
        } else {
            LeadVote::create(array_merge($attributes, [
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
            ]));
        }

        $this->refreshLeadCounts($lead);
        $this->reset(['downvotingLeadId', 'downvoteReason', 'downvoteExplanation']);
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

        $viewerId = Auth::id();
        $downvoteNotes = [];

        foreach ($leads as $lead) {
            if ($viewerId && $lead->user_id === $viewerId) {
                $downvoteNotes[$lead->id] = DownvoteNotes::forLead($lead);
            }
        }

        return view('livewire.show-request', [
            'leads' => $leads,
            'awardedLead' => $leads->first(fn (Lead $lead) => $lead->isAwarded()),
            'downvoteNotes' => $downvoteNotes,
            'downvoteReasons' => DownvoteReason::cases(),
        ])->layout('layouts.app');
    }

    protected function leadOnThisRequest(int $leadId): Lead
    {
        return Lead::query()
            ->where('question_id', $this->itemRequest->id)
            ->findOrFail($leadId);
    }

    protected function existingVote(Lead $lead): ?LeadVote
    {
        return LeadVote::query()
            ->where('lead_id', $lead->id)
            ->where('user_id', Auth::id())
            ->first();
    }

    protected function refreshLeadCounts(Lead $lead): void
    {
        $lead->update([
            'upvotes_count' => $lead->votes()->where('type', 1)->count(),
            'downvotes_count' => $lead->votes()->where('type', -1)->count(),
            'last_verified_at' => now(),
        ]);
    }
}
