<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-2xl mx-auto px-4">
        <div class="mb-6">
            <a href="{{ route('requests.show', $question) }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition">
                ← Back to request
            </a>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-800">Edit your lead</h2>
            <p class="text-sm text-gray-500 mt-2 mb-6">
                Updating the store, price, or location for
                <a href="{{ route('requests.show', $question) }}" wire:navigate class="font-semibold text-indigo-600 hover:underline">{{ $question->title }}</a>.
            </p>

            @error('delete')
                <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm font-medium rounded-lg">{{ $message }}</div>
            @enderror

            <form wire:submit.prevent="save" class="space-y-4">
                @include('livewire.leads.partials.fields')

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('posts.manage') }}" wire:navigate class="px-5 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition">Cancel</a>
                    <button type="submit" class="px-5 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition">Save lead</button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-100">
                @if($lead->isAwarded())
                    <p class="text-sm text-amber-800 mb-3">The requester verified a purchase on this lead. They have to remove that award before it can be deleted.</p>
                @endif
                <button type="button" wire:click="delete" wire:confirm="Delete this lead?" class="text-sm font-semibold text-red-600 hover:text-red-700">
                    Delete this lead
                </button>
            </div>
        </div>
    </div>
</div>
