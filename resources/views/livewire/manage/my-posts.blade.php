<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Your posts</h1>
                <p class="text-sm text-gray-500 mt-2 max-w-2xl">Edit a question’s photo or description until a lead comes in. After that, post a specification update. You can also correct or remove leads you shared.</p>
            </div>
            <a href="{{ route('requests.create') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition">
                New request
            </a>
        </div>

        @if(session('manage_message'))
            <div class="p-3 bg-emerald-50 text-emerald-800 text-sm font-semibold rounded-xl">{{ session('manage_message') }}</div>
        @endif
        @error('delete')
            <div class="p-3 bg-red-50 text-red-700 text-sm font-medium rounded-xl">{{ $message }}</div>
        @enderror

        <section class="space-y-4">
            <h2 class="text-lg font-bold text-gray-900">Questions you posted</h2>

            @forelse($questions as $question)
                <article class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex gap-4">
                        @if($question->image_path)
                            <img src="{{ asset('storage/'.$question->image_path) }}" alt="{{ $question->title }}" class="w-20 h-20 object-cover rounded-xl border border-gray-100 flex-shrink-0">
                        @else
                            <div class="w-20 h-20 flex-shrink-0 bg-gray-50 border border-gray-100 rounded-xl flex items-center justify-center text-gray-300">📦</div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="px-2.5 py-1 font-semibold rounded-full bg-indigo-50 text-indigo-700 uppercase">{{ $question->category->name ?? 'General' }}</span>
                                @if($question->leads_count === 0)
                                    <span class="px-2.5 py-1 font-semibold rounded-full bg-emerald-50 text-emerald-700">Editable</span>
                                @else
                                    <span class="px-2.5 py-1 font-semibold rounded-full bg-slate-100 text-slate-700">Photo and description locked</span>
                                @endif
                                @if($question->purchase_verified)
                                    <span class="px-2.5 py-1 font-semibold rounded-full bg-amber-50 text-amber-800"><i class="fa-solid fa-star"></i> Purchase verified</span>
                                @endif
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 mt-2">
                                <a href="{{ route('requests.show', ['request' => $question, 'from' => 'posts']) }}" wire:navigate class="hover:text-indigo-600">{{ $question->title }}</a>
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $question->leads_count }} {{ \Illuminate\Support\Str::plural('lead', $question->leads_count) }} · {{ $question->created_at->diffForHumans() }}</p>

                            <div class="mt-3">
                                @if($question->leads_count === 0)
                                    <a href="{{ route('requests.edit', ['request' => $question, 'from' => 'posts']) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:underline">Edit photo or description</a>
                                @else
                                    <a href="{{ route('requests.show', ['request' => $question, 'from' => 'posts']) }}#specifications-{{ $question->id }}" class="text-sm font-semibold text-indigo-600 hover:underline">View on the request</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($question->leads_count > 0)
                        <livewire:questions.follow-ups :question="$question" :from-posts="true" :key="'manage-follow-ups-'.$question->id" />
                    @endif
                </article>
            @empty
                <div class="bg-white border border-dashed border-gray-200 rounded-2xl p-8 text-center text-sm text-gray-500">
                    You have not posted a question yet.
                </div>
            @endforelse
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-bold text-gray-900">Leads you posted</h2>

            @forelse($leads as $lead)
                <article class="bg-white p-6 rounded-2xl border {{ $lead->isAwarded() ? 'border-amber-300' : 'border-gray-100' }} shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-slate-100 text-slate-800">{{ $lead->store_name }}</span>
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-50 text-emerald-700">₱{{ number_format($lead->price, 2) }}</span>
                                @if($lead->isAwarded())
                                    <span class="px-2 py-0.5 text-xs font-bold rounded bg-amber-50 text-amber-800"><i class="fa-solid fa-star"></i> Verified purchase</span>
                                @endif
                            </div>
                            <a href="{{ route('requests.show', ['request' => $lead->question_id, 'from' => 'posts']) }}" wire:navigate class="font-semibold text-gray-900 hover:text-indigo-600">
                                {{ $lead->question->title ?? 'Request' }}
                            </a>
                            <p class="text-xs text-gray-400 mt-1">{{ $lead->created_at->diffForHumans() }}</p>
                            @if($lead->isAwarded())
                                <p class="text-xs text-amber-800 mt-2">The requester verified a purchase here, so this lead stays until they remove the award.</p>
                            @endif
                            @include('livewire.leads.partials.downvote-notes', ['notes' => $downvoteNotes[$lead->id] ?? collect()])
                        </div>
                        <div class="flex items-center gap-3 text-sm font-semibold">
                            <a href="{{ route('leads.edit', $lead) }}" wire:navigate class="text-indigo-600 hover:underline">Edit</a>
                            <button type="button" wire:click="deleteLead({{ $lead->id }})" wire:confirm="Delete this lead?" class="text-red-600 hover:text-red-700">Delete</button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white border border-dashed border-gray-200 rounded-2xl p-8 text-center text-sm text-gray-500">
                    You have not posted a lead yet.
                </div>
            @endforelse
        </section>
    </div>
</div>
