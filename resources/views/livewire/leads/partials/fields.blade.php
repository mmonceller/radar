<div>
    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Store Name <span class="text-red-500">*</span></label>
    <input type="text" wire:model="store_name" placeholder="Target, Store, Amazon..." class="w-full text-sm p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('store_name') border-red-500 @enderror">
    @error('store_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
</div>

@if($is_online)
    <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100/60 space-y-1">
        <label class="block text-xs font-bold text-blue-900 uppercase tracking-wider mb-1">Website URL <span class="text-red-500">*</span></label>
        <input type="url" wire:model="source_link" placeholder="https://amazon.com/item-path..." class="w-full text-sm p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('source_link') border-red-500 @enderror">
        @error('source_link') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
    </div>
@endif

<div>
    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Price <span class="text-red-500">*</span></label>
    <div class="relative">
        <span class="absolute left-3 top-3 text-gray-400 text-sm font-semibold">₱</span>
        <input type="number" step="0.01" wire:model="price" placeholder="0.00" class="w-full text-sm p-3 pl-7 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('price') border-red-500 @enderror">
    </div>
    @error('price') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
</div>

<div>
    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Shop Type</label>
    <div class="grid grid-cols-2 gap-2 p-1 bg-gray-50 rounded-xl border border-gray-100">
        <button type="button" wire:click="changeShopType(false)" class="py-2 text-xs font-bold rounded-lg transition {{ !$is_online ? 'bg-white shadow text-indigo-600' : 'text-gray-500' }}">
            <i class="fa-solid fa-store"></i> Brick & Mortar
        </button>
        <button type="button" wire:click="changeShopType(true)" class="py-2 text-xs font-bold rounded-lg transition {{ $is_online ? 'bg-white shadow text-indigo-600' : 'text-gray-500' }}">
            <i class="fa-solid fa-globe"></i> Online Store
        </button>
    </div>
</div>

@if(!$is_online)
    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-3">
        <div class="text-xs font-bold text-slate-700 flex items-center gap-1">
            <i class="fa-solid fa-location-dot"></i> Pin Coordinates <span class="text-red-500">*</span>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <input type="text" wire:model="latitude" placeholder="Latitude" class="w-full text-xs p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('latitude') border-red-500 @enderror">
                @error('latitude') <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <input type="text" wire:model="longitude" placeholder="Longitude" class="w-full text-xs p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('longitude') border-red-500 @enderror">
                @error('longitude') <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
            </div>
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Street Address Details (Optional)</label>
            <input type="text" wire:model="address" placeholder="e.g., Greenbelt Mall, Wing B" class="w-full text-xs p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>
@endif

<div>
    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Notes / Description <span class="text-gray-400 font-normal">(Optional)</span></label>
    <textarea wire:model="description" rows="3" placeholder="Stock status, availability notes, shelf directions..." class="w-full text-sm p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"></textarea>
    @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
</div>
