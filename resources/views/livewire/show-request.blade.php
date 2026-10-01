<div class="max-w-5xl mx-auto px-4 py-8">
    
    <div class="mb-6">
        <a href="{{ $fromPosts ? route('posts.manage') : route('home') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition">
            ← {{ $fromPosts ? 'Back to My Posts' : 'Back to Search' }}
        </a>
    </div>

    @if(session('question_updated'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 text-sm font-semibold rounded-xl">{{ session('question_updated') }}</div>
    @endif
    @if(session('follow_up_notice'))
        <div class="mb-4 p-3 bg-amber-50 text-amber-900 text-sm font-semibold rounded-xl">{{ session('follow_up_notice') }}</div>
    @endif
    @if(session('lead_updated'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 text-sm font-semibold rounded-xl">{{ session('lead_updated') }}</div>
    @endif
    @if(session('lead_message'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 text-sm font-semibold rounded-xl">{{ session('lead_message') }}</div>
    @endif

    @php $viewerCanProvideLead = auth()->check() && auth()->id() !== $itemRequest->user_id; @endphp

    <div class="grid grid-cols-1 {{ $viewerCanProvideLead ? 'lg:grid-cols-3' : '' }} gap-8">
        
        <div class="{{ $viewerCanProvideLead ? 'lg:col-span-2' : '' }} space-y-6">
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

                <livewire:questions.follow-ups :question="$itemRequest" :from-posts="$fromPosts" :key="'show-follow-ups-'.$itemRequest->id" />
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-bold text-gray-900">Community Leads ({{ $leads->count() }})</h3>

                @if($awardedLead)
                    <div class="bg-amber-50 border border-amber-200 text-amber-900 text-sm rounded-xl p-4">
                        <i class="fa-solid fa-star"></i>
                        Verified purchase at {{ $awardedLead->store_name }} for ₱{{ number_format((float) $awardedLead->verified_price, 2) }}.
                    </div>
                @endif
                
                @forelse($leads as $lead)
                    <div wire:key="lead-card-{{ $lead->id }}" class="bg-white p-6 rounded-xl border {{ $lead->isAwarded() ? 'border-amber-300 ring-1 ring-amber-200' : 'border-gray-100' }} shadow-sm flex gap-4 items-start relative">

                        @if($lead->duplicate_of_id)
                            <div class="absolute top-0 right-0 bg-amber-500 text-white text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-bl-xl rounded-tr-xl">
                                <i class="fa-solid fa-triangle-exclamation"></i> Duplicate Entry
                            </div>
                        @endif
                        
                        @php $myVote = auth()->check() ? $lead->votes->firstWhere('user_id', auth()->id()) : null; @endphp
                        <div class="flex flex-col items-center space-y-1 bg-gray-50 p-2 rounded-lg flex-shrink-0">
                            <button wire:click="vote({{ $lead->id }}, 1)" class="p-1 rounded transition {{ $myVote && (int) $myVote->type === 1 ? 'text-emerald-600' : 'text-gray-400 hover:text-emerald-600 hover:bg-emerald-50' }}"><i class="fa-regular fa-thumbs-up"></i></button>
                            <span class="text-sm font-bold {{ ($lead->upvotes_count - $lead->downvotes_count) >= 0 ? 'text-gray-700' : 'text-red-500' }}">
                                {{ $lead->upvotes_count - $lead->downvotes_count }}
                            </span>
                            <button wire:click="startDownvote({{ $lead->id }})" class="p-1 rounded transition {{ ($myVote && (int) $myVote->type === -1) || (int) $downvotingLeadId === $lead->id ? 'text-rose-600' : 'text-gray-400 hover:text-rose-600 hover:bg-rose-50' }}"><i class="fa-regular fa-thumbs-down"></i></button>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-gray-400 flex flex-wrap justify-between items-center gap-2 mb-2">
                                <div>By <span class="font-semibold text-gray-700">{{ $lead->user->name }}</span> • {{ $lead->created_at->diffForHumans() }}
                                    @if(auth()->id() === $lead->user_id)
                                        · <a href="{{ route('leads.edit', $lead) }}" wire:navigate class="text-indigo-600 font-semibold hover:underline">Edit</a>
                                    @endif
                                </div>
                                @if($lead->last_verified_at)
                                    <div class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[11px] font-medium">
                                        ⏱️ Last verified {{ $lead->last_verified_at->diffForHumans() }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-slate-100 text-slate-800"><i class="fa-solid fa-store"></i> {{ $lead->store_name }}</span>
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-50 text-emerald-700">₱{{ number_format($lead->price, 2) }}</span>
                                <span class="px-2 py-0.5 text-xs font-bold rounded {{ $lead->is_online ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                    @if($lead->is_online )
                                        <i class="fa-solid fa-globe"></i> Online
                                    @else
                                        <i class="fa-solid fa-location-dot"></i> Physical Store
                                    @endif
                                </span>
                            </div>

                            @if(!$lead->is_online && $lead->latitude)
                                <div class="text-xs text-gray-500 mb-3 bg-gray-50 p-2.5 rounded-lg border border-gray-100 flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <i class="fa-solid fa-location-dot"></i> <strong>Coordinates:</strong> {{ $lead->latitude }}, {{ $lead->longitude }} 
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
                                    <i class="fa-solid fa-link"></i>&nbsp; {{ $lead->source_link }} &nbsp;<i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            @endif

                            @if((int) $downvotingLeadId === $lead->id)
                                @include('livewire.leads.partials.downvote-form')
                            @endif

                            @if(auth()->id() === $lead->user_id)
                                @include('livewire.leads.partials.downvote-notes', ['notes' => $downvoteNotes[$lead->id] ?? collect()])
                            @endif

                            <div class="mt-4">
                                <livewire:leads.award-lead :lead="$lead" :key="'award-lead-'.$lead->id" />
                            </div>

                            <!-- CORE INFO WRAPPER COLUMN -->
                            <div class="flex-1 min-w-0">
                                <!-- (Your existing header badge strings, prices, map variables stay here...) -->

                                @if($lead->duplicate_of_id)
                                    <div class="mt-1 mb-3 text-xs bg-amber-50 text-amber-800 p-2.5 rounded-lg border border-amber-200/60 font-medium">
                                        🔗 Flagged as duplicate of: <span class="font-bold text-slate-800">"{{ $lead->originalLead->store_name ?? 'Original Record' }}"</span> 
                                        (₱{{ number_format($lead->originalLead->price ?? 0, 2) }})
                                    </div>
                                @endif

                                <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">{{ $lead->description }}</p>

                                <!-- DYNAMIC REPORT AS DUPLICATE PANEL ACTION -->
                                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                    <div></div> <!-- Spacer -->
                                    
                                    @if($leads->count() > 1 && !$lead->duplicate_of_id)
                                        <button type="button" wire:click="toggleReportDuplicateForm({{ $lead->id }})" class="text-gray-400 hover:text-amber-600 font-bold transition flex items-center gap-1">
                                            🛡️ {{ $this->reportingLeadId === $lead->id ? 'Cancel Action' : 'Report Duplicate' }}
                                        </button>
                                    @endif
                                </div>

                                <!-- FLOATING SELECTOR PANEL FOR ASSIGNING MATCHES -->
                                @if($this->reportingLeadId === $lead->id)
                                    <div class="mt-3 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3 animate-fade-in">
                                        <div class="text-xs font-bold text-slate-700">Select the original upvoted lead:</div>
                                        
                                        @if(session()->has('duplicate_error_' . $lead->id))
                                            <div class="text-[11px] text-red-600 font-bold bg-red-50 p-2 rounded-lg">
                                                {{ session('duplicate_error_' . $lead->id) }}
                                            </div>
                                        @endif

                                        <div class="flex gap-2">
                                            <select wire:model="selectedOriginalId" class="flex-1 text-xs p-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500">
                                                <option value="">-- Choose matching upvoted lead --</option>
                                                @foreach($leads as $option)
                                                    @php 
                                                        $optionScore = $option->upvotes_count - $option->downvotes_count; 
                                                    @endphp
                                                    <!-- Filters: Cannot select self, and must have a positive validation score status -->
                                                    @if($option->id !== $lead->id && $optionScore > 0)
                                                        <option value="{{ $option->id }}">
                                                            🏢 {{ $option->store_name }} (₱{{ number_format($option->price, 2) }}) - [Score: +{{ $optionScore }}] By {{ $option->user->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            
                                            <button type="button" wire:click="submitDuplicateReport" class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs transition shadow-sm">
                                                Confirm Link
                                            </button>
                                        </div>
                                        @error('selectedOriginalId') <span class="text-red-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                            </div>
                        </div>

                        

                    </div>
                @empty
                    <div class="bg-gray-50 border border-dashed rounded-xl p-8 text-center text-gray-400 text-sm">
                        No active supplier location leads have been submitted for this item yet.
                    </div>
                @endforelse
            </div>
        </div>

        @if($viewerCanProvideLead)
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-6">
                <h3 class="text-lg font-bold text-gray-900 border-b border-gray-50 pb-3 mb-4">Provide a Location Lead</h3>

                <form wire:submit.prevent="submitLead" class="space-y-4">
                    @include('livewire.leads.partials.fields')

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm shadow-sm transition">
                        Submit Verified Lead Info
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>