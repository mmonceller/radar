<form wire:submit="submitDownvote" class="mt-4 p-4 bg-rose-50 border border-rose-100 rounded-xl space-y-3">
    <div class="text-xs font-bold text-rose-900">What was wrong with this lead?</div>

    <div class="space-y-2">
        @foreach($downvoteReasons as $reason)
            <label class="flex items-start gap-2 text-xs text-rose-950">
                <input type="radio" wire:model="downvoteReason" value="{{ $reason->value }}" class="mt-0.5 text-rose-600">
                <span>{{ $reason->label() }}</span>
            </label>
        @endforeach
    </div>
    @error('downvoteReason') <span class="text-red-600 text-xs font-semibold block">{{ $message }}</span> @enderror

    <div>
        <label class="block text-[11px] font-semibold text-rose-800 mb-1">Brief explanation (optional)</label>
        <textarea wire:model="downvoteExplanation" rows="2" maxlength="280" placeholder="A short note about what you found" class="w-full text-xs p-2.5 border border-rose-100 rounded-lg focus:ring-2 focus:ring-rose-400 @error('downvoteExplanation') border-red-500 @enderror"></textarea>
        @error('downvoteExplanation') <span class="text-red-600 text-xs font-semibold block">{{ $message }}</span> @enderror
    </div>

    <div class="flex gap-2">
        <button type="submit" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg">Submit downvote</button>
        <button type="button" wire:click="cancelDownvote" class="px-3 py-2 bg-white text-gray-600 text-xs font-bold rounded-lg border border-gray-200">Cancel</button>
    </div>
</form>
