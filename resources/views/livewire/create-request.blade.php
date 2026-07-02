<div id="form-page-wrapper" class="py-12 bg-gray-50 min-h-screen">
    <div id="form-container" class="max-w-2xl mx-auto px-4">
        <div id="form-card" class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">What item are you looking for?</h2>

            <form wire:submit.prevent="save" class="space-y-6">
                <div id="input-group-title">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Item Name / Title</label>
                    <input type="text" wire:model="title" placeholder="e.g., Nintendo Switch OLED" 
                           class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('title') border-red-500 @enderror">
                    @error('title') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- ID: input-group-category -->
                <div id="input-group-category">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    
                    <!-- Bind to category_id instead of category -->
                    <select wire:model="category_id" class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('category_id') border-red-500 @enderror">
                        <option value="">Select a Category</option>
                        
                        <!-- Loop through the database records -->
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    
                    @error('category_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div id="input-group-description">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Extra Details (Optional)</label>
                    <textarea wire:model="description" rows="4" placeholder="Specify colors, model numbers, or requirements..."
                              class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div id="input-group-image">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Attach Reference Photo (Optional)</label>
                    <input type="file" wire:model="image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    
                    @if ($image)
                        <div id="image-preview-box" class="mt-4">
                            <p class="text-xs text-gray-500 mb-1">Preview:</p>
                            <img src="{{ $image->temporaryUrl() }}" class="w-32 h-32 object-cover rounded-xl border">
                        </div>
                    @endif
                    @error('image') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div id="form-actions-bar" class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('home') }}" wire:navigate class="px-5 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition">Cancel</a>
                    <button type="submit" class="px-5 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition">Post Request</button>
                </div>
            </form>
        </div>
    </div>
</div>