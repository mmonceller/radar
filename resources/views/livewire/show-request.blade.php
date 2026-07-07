<div class="max-w-5xl mx-auto px-4 py-8">
    
    <div class="mb-6">
        <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition">
            ← Back to Search
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100">
                
                <div class="flex items-center justify-between border-b border-gray-50 pb-4 mb-6">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700 uppercase">
                        {{ $itemRequest->category->name ?? 'General' }}
                    </span>
                    <span class="text-xs text-gray-400">
                        Posted {{ $itemRequest->created_at->diffForHumans() }}
                    </span>
                </div>

                @if($itemRequest->image_path)
                    <div class="w-full max-h-[400px] overflow-hidden rounded-xl border border-gray-100 mb-6 bg-gray-50">
                        <img src="{{ asset('storage/' . $itemRequest->image_path) }}" alt="{{ $itemRequest->title }}" class="w-full h-full object-contain mx-auto">
                    </div>
                @endif

                <h1 class="text-2xl font-black text-gray-900 leading-tight">{{ $itemRequest->title }}</h1>
                <div class="text-sm text-gray-500 mt-1">Requested by <span class="font-semibold text-gray-700">{{ $itemRequest->user->name }}</span></div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <p class="text-gray-600 text-sm md:text-base whitespace-pre-line leading-relaxed">{{ $itemRequest->description }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-bold text-gray-900">Community Leads ({{ $leads->count() }})</h3>
                
                @forelse($leads as $lead)
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex gap-4 items-start">
                        
                        <div class="flex flex-col items-center space-y-1 bg-gray-50 p-2 rounded-lg flex-shrink-0">
                            <button wire:click="vote({{ $lead->id }}, 1)" class="p-1 rounded text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition">👍</button>
                            <span class="text-sm font-bold {{ ($lead->upvotes_count - $lead->downvotes_count) >= 0 ? 'text-gray-700' : 'text-red-500' }}">
                                {{ $lead->upvotes_count - $lead->downvotes_count }}
                            </span>
                            <button wire:click="vote({{ $lead->id }}, -1)" class="p-1 rounded text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition">👎</button>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-gray-400 flex flex-wrap justify-between items-center gap-2 mb-2">
                                <div>By <span class="font-semibold text-gray-700">{{ $lead->user->name }}</span> • {{ $lead->created_at->diffForHumans() }}</div>
                                @if($lead->last_verified_at)
                                    <div class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[11px] font-medium">
                                        ⏱️ Last verified {{ $lead->last_verified_at->diffForHumans() }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-slate-100 text-slate-800">🏬 {{ $lead->store_name }}</span>
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-50 text-emerald-700">₱{{ number_format($lead->price, 2) }}</span>
                                <span class="px-2 py-0.5 text-xs font-bold rounded {{ $lead->is_online ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $lead->is_online ? '🌐 Online' : '📍 Physical Store' }}
                                </span>
                            </div>

                            @if(!$lead->is_online && $lead->latitude)
                                <div class="text-xs text-gray-500 mb-3 bg-gray-50 p-2.5 rounded-lg border border-gray-100 flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        📍 <strong>Coordinates:</strong> {{ $lead->latitude }}, {{ $lead->longitude }} 
                                        @if($lead->address) | <span class="italic">{{ $lead->address }}</span> @endif
                                    </div>
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $lead->latitude }},{{ $lead->longitude }}" target="_blank" class="text-indigo-600 hover:underline font-semibold text-[11px]">
                                        Open Map ↗
                                    </a>
                                </div>
                            @endif

                            <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">{{ $lead->description }}</p>

                            @if($lead->source_link)
                                <a href="{{ $lead->source_link }}" target="_blank" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:underline mt-3">
                                    🔗 View Link Source ↗
                                </a>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="bg-gray-50 border border-dashed rounded-xl p-8 text-center text-gray-400 text-sm">
                        No active supplier location leads have been submitted for this item yet.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-6">
                <h3 class="text-lg font-bold text-gray-900 border-b border-gray-50 pb-3 mb-4">Provide a Location Lead</h3>
                
                @if (session()->has('lead_message'))
                    <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 text-xs font-semibold rounded-lg">
                        {{ session('lead_message') }}
                    </div>
                @endif

                <form wire:submit.prevent="submitLead" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Store Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="store_name" placeholder="Target, Store, Amazon..." class="w-full text-sm p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('store_name') border-red-500 @enderror">
                        @error('store_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if($this->is_online)
                        <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100/60 space-y-1 animate-fade-in">
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
                            <button type="button" wire:click="changeShopType(false)" class="py-2 text-xs font-bold rounded-lg transition {{ !$this->is_online ? 'bg-white shadow text-indigo-600' : 'text-gray-500' }}">
                                🏢 Brick & Mortar
                            </button>
                            <button type="button" wire:click="changeShopType(true)" class="py-2 text-xs font-bold rounded-lg transition {{ $this->is_online ? 'bg-white shadow text-indigo-600' : 'text-gray-500' }}">
                                🌐 Online Store
                            </button>
                        </div>
                    </div>

                    @if(!$this->is_online)
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-3">
                            <div class="text-xs font-bold text-slate-700 flex items-center gap-1">
                                📍 Pin Coordinates <span class="text-red-500">*</span>
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

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm shadow-sm transition">
                        Submit Verified Lead Info
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>