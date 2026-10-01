<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-2xl mx-auto px-4">
        <div class="mb-6">
            <a href="{{ $fromPosts ? route('posts.manage') : route('requests.show', $question) }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition">
                ← {{ $fromPosts ? 'Back to My Posts' : 'Back to request' }}
            </a>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-800">Edit photo and description</h2>
            <p class="text-sm text-gray-500 mt-2">{{ $question->title }}</p>
            <p class="text-sm text-gray-500 mt-1 mb-6">You can change these until someone posts a lead. The title stays the same.</p>

            <form wire:submit.prevent="save" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea wire:model="description" rows="5" placeholder="Colors, model numbers, or other requirements..."
                              class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reference photo</label>

                    @if($question->image_path && ! $image)
                        <img src="{{ asset('storage/'.$question->image_path) }}" alt="{{ $question->title }}" class="mb-3 h-32 w-32 object-cover rounded-xl border">
                    @endif

                    <input type="file" wire:model="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <div wire:loading wire:target="image" class="text-xs text-gray-500 mt-2">Uploading photo…</div>

                    @if($image)
                        <div class="mt-4">
                            <p class="text-xs text-gray-500 mb-1">New photo:</p>
                            <img src="{{ $image->temporaryUrl() }}" alt="New reference preview" class="w-32 h-32 object-cover rounded-xl border">
                        </div>
                    @endif
                    @error('image') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                    @if($question->image_path)
                        <label class="mt-4 flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" wire:model="removeImage" class="rounded border-gray-300 text-indigo-600">
                            Remove the current photo
                        </label>
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ $fromPosts ? route('posts.manage') : route('requests.show', $question) }}" wire:navigate class="px-5 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition">Cancel</a>
                    <button type="submit" class="px-5 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
