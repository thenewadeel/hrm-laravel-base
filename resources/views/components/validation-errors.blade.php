@if ($errors->any())
    <div {{ $attributes }}>
        <div class="rounded-lg border border-error/20 bg-error/10 px-4 py-3">
            <div class="flex items-center gap-2 font-semibold text-sm text-error">
                <x-heroicon-m-exclamation-circle class="size-4 shrink-0" />
                {{ __('Please fix the fields highlighted below.') }}
            </div>

            <ul class="mt-2 list-disc list-inside space-y-1 text-xs text-error">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif