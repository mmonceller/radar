<div>
    @if($lead->isAwarded())
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-center gap-2 text-amber-800 font-bold text-sm">
                <i class="fa-solid fa-star"></i> Verified purchase
            </div>
            <p class="text-sm text-amber-900 mt-2">
                Paid ₱{{ number_format((float) $lead->verified_price, 2) }}
                @if($lead->purchase_verified_at)
                    · {{ $lead->purchase_verified_at->diffForHumans() }}
                @endif
            </p>
            @if($lead->verification_image_path)
                <img src="{{ asset('storage/'.$lead->verification_image_path) }}" alt="Purchase verification for {{ $lead->store_name }}" class="mt-3 max-h-56 w-full object-contain rounded-lg border border-amber-100 bg-white">
            @endif
            @if($isOwner)
                <button type="button" wire:click="revoke" wire:confirm="Remove this verified purchase?" class="mt-3 text-xs font-bold text-amber-800 hover:text-amber-950 underline">
                    Remove award
                </button>
            @endif
        </div>
    @elseif($isOwner && $anotherAwarded)
        <p class="text-xs text-gray-500">You already verified a purchase on another lead for this request.</p>
    @elseif($isOwner)
        @if(! $showForm)
            <button type="button" wire:click="openForm" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 text-amber-800 text-xs font-bold hover:bg-amber-100 transition">
                <i class="fa-regular fa-star"></i> I bought this
            </button>
            <p class="text-[11px] text-gray-500 mt-2">Award the lead you actually bought from, and upload a photo that shows the price.</p>
        @else
            <form wire:submit="submit" class="space-y-3 rounded-xl border border-amber-200 bg-amber-50/60 p-4">
                <div class="text-sm font-bold text-amber-900">Verify this purchase</div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Price you paid</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm font-semibold">₱</span>
                        <input type="number" step="0.01" wire:model="verifiedPrice" class="w-full text-sm p-2.5 pl-7 border border-gray-200 rounded-lg @error('verifiedPrice') border-red-500 @enderror">
                    </div>
                    @error('verifiedPrice') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Photo showing the item and price</label>
                    <input type="file" wire:model="verificationImage" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-white file:text-amber-800">
                    <div wire:loading wire:target="verificationImage" class="text-[11px] text-gray-500 mt-1">Uploading photo…</div>
                    @if($verificationImage)
                        <img src="{{ $verificationImage->temporaryUrl() }}" alt="Verification preview" class="mt-2 h-24 w-24 object-cover rounded-lg border">
                    @endif
                    @error('verificationImage') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg" wire:loading.attr="disabled" wire:target="submit">
                        Award and verify
                    </button>
                    <button type="button" wire:click="cancel" class="px-3 py-2 bg-white text-gray-600 text-xs font-bold rounded-lg border border-gray-200">
                        Cancel
                    </button>
                </div>
            </form>
        @endif
    @endif
</div>
