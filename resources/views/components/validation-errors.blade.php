@if ($errors->any())
    <div {{ $attributes }}>
        <div class="rounded-lg border border-error/20 bg-error/10 px-4 py-3">
            <div class="flex items-center gap-2 font-semibold text-sm text-error">
                <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
                        clip-rule="evenodd" />
                </svg>
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