<div class="flex flex-wrap gap-1">
    @foreach ($getVisibleTags() as $tag)
        <x-ui-badge color="blue" size="sm">{{ $tag }}</x-ui-badge>
    @endforeach
    
    @if ($getRemainingCount() > 0)
        <x-ui-badge color="gray" size="sm">+{{ $getRemainingCount() }} more</x-ui-badge>
    @endif
</div>