<div id="main-page-wrapper" class="py-12 bg-gray-50 min-h-screen">
    <div id="content-container" class="max-w-4xl mx-auto px-4">

        <div id="hero-search-box" class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center">
            <label class="block text-2xl font-bold text-gray-800 mb-4">I'm looking for...</label>
            
            <div id="search-input-group" class="flex flex-col sm:flex-row gap-3 items-center">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="e.g., PS5 Pro, organic dragon fruit, mechanical keyboard..."
                    class="w-full text-lg p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                >
                
                <button 
                    wire:click="toggleFilters"
                    class="w-full sm:w-auto px-5 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition duration-150 ease-in-out"
                >
                    Filters
                </button>
            </div>

            @if($showFilters)
                <div id="advanced-filters-drawer" class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200 text-left transition-all">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Category</label>
                    <select wire:model.live="category" class="w-full p-2 border border-gray-300 rounded-lg">
                        <option value="">All Categories</option>
                        <option value="electronics">Electronics</option>
                        <option value="groceries">Groceries</option>
                        <option value="fashion">Fashion</option>
                    </select>
                </div>
            @endif

            <div id="toggle-feed-link" class="mt-6 text-center">
                <button wire:click="toggleLatest" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium underline transition">
                    {{ $showLatest ? 'Hide recent requests' : 'Show the latest items other users are looking for ↓' }}
                </button>
            </div>
        </div>

        @if($showLatest || !empty($search))
            <div id="active-requests-section" class="mt-12">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Search Results</h3>
                
                @if($questions->isEmpty())
                    <div id="empty-results-notice" class="bg-white p-8 rounded-xl text-center border border-gray-200 shadow-sm">
                        <p class="text-gray-600 mb-4 font-medium">There are no similar requests matching your search.</p>
                        
                        @auth
                            <a href="{{ route('requests.create') }}" wire:navigate class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition">
                                Would you like to create a new request for this?
                            </a>
                        @else
                            <p class="text-sm text-gray-500">
                                <a href="{{ route('login') }}" wire:navigate class="text-indigo-600 underline font-medium">Log in</a> or 
                                <a href="{{ route('register') }}" wire:navigate class="text-indigo-600 underline font-medium">Register</a> to post this as a new request.
                            </p>
                        @endauth
                    </div>
                @else
                    <div id="requests-grid" class="grid gap-4 md:grid-cols-2">
                        @foreach($questions as $question)
                        <div id="request-card-{{ $question->id }}" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition flex gap-6">
                        
                        @if($question->image_path)
                            <div class="w-24 h-24 flex-shrink-0">
                                <img src="{{ asset('storage/' . $question->image_path) }}" 
                                    alt="{{ $question->title }}" 
                                    class="w-full h-full object-cover rounded-xl border border-gray-100">
                            </div>
                        @else
                            <div class="w-24 h-24 flex-shrink-0 bg-gray-50 border border-gray-100 rounded-xl flex items-center justify-center text-gray-300">
                                📦
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700 uppercase">
                                    {{ $question->category->name ?? 'General' }}
                                </span>
                                <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                                    {{ $question->created_at->diffForHumans() }}
                                </span>
                            </div>
                            
                            <h2 class="text-xl font-bold text-gray-900 mt-2 truncate hover:text-indigo-600 transition">
                                <a href="{{ route('requests.show', $question->id) }}" wire:navigate class="block">
                                    {{ $question->title }}
                                </a>
                            </h2>
                            
                            <p class="text-gray-500 text-sm truncate mt-1">
                                {{ $question->description ?? 'No extra details provided.' }}
                            </p>

                            <div class="mt-4 pt-3 border-t border-gray-50 flex justify-between items-center text-xs text-gray-500">
                                <div>By: <span class="font-medium text-gray-700">{{ $question->user->name }}</span></div>
                                <div class="text-indigo-600 font-medium">💬 {{ $question->answers_count ?? $question->answers->count() }} leads found</div>
                            </div>
                        </div>

                    </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>