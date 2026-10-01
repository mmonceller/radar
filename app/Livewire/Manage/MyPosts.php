<?php

namespace App\Livewire\Manage;

use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyPosts extends Component
{
    public function deleteLead(int $leadId): void
    {
        $lead = Lead::query()->findOrFail($leadId);

        abort_unless($lead->user_id === Auth::id(), 403);

        if ($lead->awarded_at) {
            $this->addError('delete', 'This lead has a verified purchase. The requester has to remove that award before the lead can be deleted.');

            return;
        }

        $lead->delete();

        session()->flash('manage_message', 'Lead deleted.');
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.manage.my-posts', [
            'questions' => $user->questions()
                ->with('category')
                ->withCount('leads')
                ->withExists([
                    'leads as purchase_verified' => fn ($query) => $query->whereNotNull('purchase_verified_at'),
                ])
                ->latest()
                ->get(),
            'leads' => $user->leads()
                ->with('question')
                ->latest()
                ->get(),
        ])->layout('layouts.app');
    }
}
