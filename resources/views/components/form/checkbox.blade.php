@props(['value' => 1])

<input type="checkbox" value="{{ $value }}" {!! $attributes->merge([
    'class' => 'rounded border-secondary accent-primary shadow-sm focus:ring-primary dark:ring-offset-primary',
]) !!}>