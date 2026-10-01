<?php

namespace App\Leads;

enum DownvoteReason: string
{
    case LocationOrLink = 'location_or_link';
    case StoreClosed = 'store_closed';
    case PriceMismatch = 'price_mismatch';
    case EverythingWrong = 'everything_wrong';
    case DifferentModel = 'different_model';

    public function label(): string
    {
        return match ($this) {
            self::LocationOrLink => 'The location or link is wrong',
            self::StoreClosed => 'The store is permanently closed or does not exist',
            self::PriceMismatch => 'The price is far too different in the actual store',
            self::EverythingWrong => 'Everything about this lead is wrong',
            self::DifferentModel => 'This points to a different model of the product',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
