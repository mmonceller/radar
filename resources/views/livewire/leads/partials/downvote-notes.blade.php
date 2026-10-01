@if($notes->isNotEmpty())
    <div class="mt-4 rounded-xl border border-rose-100 bg-rose-50 p-3">
        <div class="text-xs font-bold text-rose-800">Downvote notes</div>
        <p class="text-[11px] text-rose-700 mt-0.5">Only you can see why people downvoted this lead.</p>
        <ul class="mt-2 space-y-2">
            @foreach($notes as $note)
                <li class="text-xs text-rose-950">
                    <span class="font-semibold">{{ $note['label'] }}</span>
                    @if($note['explanation'])
                        <p class="mt-0.5 whitespace-pre-line text-rose-800">{{ $note['explanation'] }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
