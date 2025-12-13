@props(['text' => null])

@if($text)
    <p class="text-sm text-gray-500 mt-1">{{ $text }}</p>
@endif