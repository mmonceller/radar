<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Question;
use App\Models\Lead;
use App\Models\LeadVote;
use Illuminate\Support\Facades\Auth;

class ShowRequest extends Component
{
    // Holds the core request post data context
    public Question $itemRequest; 

    // Explicitly declared form property inputs with baseline defaults
    public $store_name = '';
    public $price = '';
    public $is_online = false; // false = Brick & Mortar, true = Online
    public $latitude = '';
    public $longitude = '';
    public $address = '';
    public $description = '';
    public $source_link = '';

    // Mount runs once on initial HTTP page load
    public function mount(Question $request)
    {
        $this->itemRequest = $request->load(['category', 'user']);
    }

    // Hand-rolled switcher to explicitly enforce boolean assignment safely
    public function changeShopType($status)
    {
        $this->is_online = (bool) $status;
    }

    public function submitLead()
    {
        // 1. Core structural rules
        $rules = [
            'store_name'  => 'required|min:2|max:255',
            'price'       => 'required|numeric|min:0',
            'is_online'   => 'required|boolean',
            'description' => 'nullable|max:1000',
        ];

        // 2. Conditional rules based on store type choice
        if ($this->is_online) {
            // Required for Online Stores
            $rules['source_link'] = 'required|url';
            $rules['address']     = 'nullable'; 
        } else {
            // Required for Brick & Mortar shops
            $rules['latitude']    = 'required|numeric';
            $rules['longitude']   = 'required|numeric';
            $rules['address']     = 'nullable|max:500';
            $rules['source_link'] = 'nullable';
        }

        $this->validate($rules);

        // 3. Save to database
        Lead::create([
            'question_id' => $this->itemRequest->id,
            'user_id'     => Auth::id(),
            'store_name'  => $this->store_name,
            'price'       => $this->price,
            'is_online'   => $this->is_online,
            'latitude'    => !$this->is_online ? $this->latitude : null,
            'longitude'   => !$this->is_online ? $this->longitude : null,
            'address'     => !$this->is_online ? $this->address : null,
            'description' => $this->description ?: null,
            'source_link' => $this->is_online ? $this->source_link : null, // Wipe link if brick & mortar
        ]);

        $this->reset(['store_name', 'price', 'is_online', 'latitude', 'longitude', 'address', 'description', 'source_link']);
        session()->flash('lead_message', 'Your structured lead has been verified!');
    }

    public function vote($leadId, $type)
    {
        $userId = Auth::id();
        $lead = Lead::findOrFail($leadId);

        $existingVote = LeadVote::where('lead_id', $leadId)->where('user_id', $userId)->first();

        if ($existingVote) {
            if ($existingVote->type == $type) {
                $existingVote->delete(); // Retract vote if tapped twice
            } else {
                $existingVote->update(['type' => $type]); // Switch direction
            }
        } else {
            LeadVote::create([
                'lead_id' => $leadId,
                'user_id' => $userId,
                'type'    => $type
            ]);
        }

        // Keep totals cached on the row & drop timestamp anchor
        $lead->update([
            'upvotes_count'    => $lead->votes()->where('type', 1)->count(),
            'downvotes_count'  => $lead->votes()->where('type', -1)->count(),
            'last_verified_at' => now(),
        ]);
    }

    public function render()
    {
        // Grab answers sorted dynamically by net total score values
        $leads = $this->itemRequest->leads()
            ->with('user')
            ->orderByRaw('(upvotes_count - downvotes_count) DESC')
            ->latest()
            ->get();

        return view('livewire.show-request', ['leads' => $leads])
            ->layout('layouts.app');
    }
}