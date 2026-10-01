<div>
    @if($followUps->isNotEmpty() || $canEdit || $canFollowUp)
        <div id="specifications-{{ $question->id }}" class="mt-6 pt-6 border-t border-gray-100 space-y-4">
            @if($followUps->isNotEmpty())
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Specification updates</h3>
                    <p class="text-xs text-gray-500 mt-1">The original photo and description stay as first posted. Newer notes are what the requester needs now.</p>
                </div>

                <div class="space-y-3">
                    @foreach($followUps as $followUp)
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
                            <div class="text-[11px] text-gray-500 mb-2">
                                {{ $followUp->user->name }} · {{ $followUp->created_at->diffForHumans() }}
                                @if($followUp->updated_at->gt($followUp->created_at))
                                    · edited {{ $followUp->updated_at->diffForHumans() }}
                                @endif
                            </div>

                            @if((int) $editingId === $followUp->id)
                                <form wire:submit="updateFollowUp" class="space-y-2">
                                    <textarea wire:model="editingBody" rows="3" class="w-full text-sm p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('editingBody') border-red-500 @enderror"></textarea>
                                    @error('editingBody') <span class="text-red-500 text-xs block">{{ $message }}</span> @enderror
                                    <div class="flex gap-2">
                                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg">Save update</button>
                                        <button type="button" wire:click="cancelEdit" class="px-3 py-1.5 bg-white text-gray-600 text-xs font-bold rounded-lg border border-gray-200">Cancel</button>
                                    </div>
                                </form>
                            @else
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $followUp->body }}</p>
                                @if($canFollowUp || $canEdit)
                                    <div class="mt-3 flex gap-3 text-xs font-bold">
                                        <button type="button" wire:click="edit({{ $followUp->id }})" class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                        <button type="button" wire:click="deleteFollowUp({{ $followUp->id }})" wire:confirm="Delete this specification update?" class="text-red-600 hover:text-red-700">Delete</button>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if($canEdit)
                <a href="{{ route('requests.edit', array_filter(['request' => $question, 'from' => $fromPosts ? 'posts' : null])) }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition">
                    Edit photo or description
                </a>
                <p class="text-xs text-gray-500">No leads yet, so you can still change the original photo and description.</p>
            @elseif($canFollowUp)
                @if(session('follow_up_saved'))
                    <div class="p-3 bg-emerald-50 text-emerald-800 text-xs font-semibold rounded-lg">
                        {{ session('follow_up_saved') }}
                    </div>
                @endif

                <form wire:submit="submit" class="space-y-3">
                    <label class="block text-sm font-bold text-gray-900">Update what you need</label>
                    <p class="text-xs text-gray-500">Leads are already posted, so the original photo and description stay locked. Add a follow-up if the item specifications changed.</p>
                    <textarea wire:model="body" rows="3" placeholder="Example: it needs to be the 1TB model, any color except red." class="w-full text-sm p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('body') border-red-500 @enderror"></textarea>
                    @error('body') <span class="text-red-500 text-xs block">{{ $message }}</span> @enderror
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition">
                        Post specification update
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
