<?php

namespace App\Livewire\Leads;

use App\Leads\LeadSubmission;
use App\Models\Lead;

trait InteractsWithLeadForm
{
    public $store_name = '';

    public $price = '';

    public $is_online = false;

    public $latitude = '';

    public $longitude = '';

    public $address = '';

    public $description = '';

    public $source_link = '';

    public function changeShopType($status): void
    {
        $this->is_online = (bool) $status;
    }

    public function fillLeadFields(Lead $lead): void
    {
        $this->store_name = $lead->store_name;
        $this->price = $lead->price;
        $this->is_online = (bool) $lead->is_online;
        $this->latitude = $lead->latitude ?? '';
        $this->longitude = $lead->longitude ?? '';
        $this->address = $lead->address ?? '';
        $this->description = $lead->description ?? '';
        $this->source_link = $lead->source_link ?? '';
    }

    protected function validatedLeadAttributes(): array
    {
        $this->validate(LeadSubmission::rules((bool) $this->is_online));

        return LeadSubmission::attributes(
            (string) $this->store_name,
            $this->price,
            (bool) $this->is_online,
            $this->latitude,
            $this->longitude,
            $this->address !== '' ? $this->address : null,
            $this->description !== '' ? $this->description : null,
            $this->source_link !== '' ? $this->source_link : null,
        );
    }
}
