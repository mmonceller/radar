<?php

namespace App\Leads;

class LeadSubmission
{
    public static function rules(bool $isOnline): array
    {
        $rules = [
            'store_name' => 'required|min:2|max:255',
            'price' => 'required|numeric|min:0',
            'is_online' => 'required|boolean',
            'description' => 'nullable|max:1000',
        ];

        if ($isOnline) {
            $rules['source_link'] = 'required|url';
            $rules['address'] = 'nullable';
        } else {
            $rules['latitude'] = 'required|numeric';
            $rules['longitude'] = 'required|numeric';
            $rules['address'] = 'nullable|max:500';
            $rules['source_link'] = 'nullable';
        }

        return $rules;
    }

    public static function attributes(
        string $storeName,
        mixed $price,
        bool $isOnline,
        mixed $latitude,
        mixed $longitude,
        ?string $address,
        ?string $description,
        ?string $sourceLink,
    ): array {
        return [
            'store_name' => $storeName,
            'price' => $price,
            'is_online' => $isOnline,
            'latitude' => $isOnline ? null : $latitude,
            'longitude' => $isOnline ? null : $longitude,
            'address' => $isOnline ? null : ($address ?: null),
            'description' => $description ?: null,
            'source_link' => $isOnline ? $sourceLink : null,
        ];
    }
}
