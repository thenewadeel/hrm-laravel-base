@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'text-sm font-medium text-error']) }}>{{ $message }}</p>
@enderror