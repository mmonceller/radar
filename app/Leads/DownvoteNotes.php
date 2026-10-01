<?php

namespace App\Leads;

use App\Models\Lead;
use App\Models\LeadVote;
use Illuminate\Support\Collection;

class DownvoteNotes
{
    public static function forLead(Lead $lead): Collection
    {
        return $lead->votes
            ->filter(fn (LeadVote $vote) => (int) $vote->type === -1 && $vote->reason instanceof DownvoteReason)
            ->map(fn (LeadVote $vote) => [
                'label' => $vote->reason->label(),
                'explanation' => $vote->explanation,
            ])
            ->values();
    }
}
