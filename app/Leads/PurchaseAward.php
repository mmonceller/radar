<?php

namespace App\Leads;

use App\Models\Lead;
use App\Models\Question;
use App\Models\User;
use App\Support\StoredImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseAward
{
    public static function grant(User $buyer, Lead $lead, UploadedFile $photo, mixed $price): void
    {
        $question = Question::query()->findOrFail($lead->question_id);

        abort_unless($buyer->can('award', $question), 403);

        DB::transaction(function () use ($lead, $photo, $price, $question) {
            Question::query()->whereKey($question->id)->lockForUpdate()->first();

            $freshLead = Lead::query()->whereKey($lead->id)->lockForUpdate()->firstOrFail();

            $anotherAwarded = Lead::query()
                ->where('question_id', $question->id)
                ->whereNotNull('awarded_at')
                ->whereKeyNot($freshLead->id)
                ->lockForUpdate()
                ->exists();

            if ($anotherAwarded) {
                throw ValidationException::withMessages([
                    'verifiedPrice' => 'You already awarded another lead. Remove that award before choosing a different one.',
                ]);
            }

            if ($freshLead->awarded_at !== null) {
                throw ValidationException::withMessages([
                    'verifiedPrice' => 'This lead is already marked as a verified purchase.',
                ]);
            }

            $path = StoredImage::store($photo, 'verifications');

            try {
                $freshLead->forceFill([
                    'awarded_at' => now(),
                    'verification_image_path' => $path,
                    'verified_price' => $price,
                    'purchase_verified_at' => now(),
                ])->save();
            } catch (\Throwable $exception) {
                StoredImage::delete($path);

                throw $exception;
            }
        });
    }

    public static function revoke(User $buyer, Lead $lead): void
    {
        $question = Question::query()->findOrFail($lead->question_id);

        abort_unless($buyer->can('award', $question), 403);

        $path = $lead->verification_image_path;

        $lead->forceFill([
            'awarded_at' => null,
            'verification_image_path' => null,
            'verified_price' => null,
            'purchase_verified_at' => null,
        ])->save();

        StoredImage::delete($path);
    }
}
