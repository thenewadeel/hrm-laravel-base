<ul class="space-y-4">
    @foreach ($units as $unit)
        <li wire:key="unit-{{ $unit->id }}" x-data="{ open: true }" class="relative pl-6">
            <div x-data="{ dragging: false }" @dragover.prevent.stop="dragging = true"
                @dragleave.prevent.stop="dragging = false"
                @drop.prevent.stop="
                    dragging = false;
                    const userId = event.dataTransfer.getData('text/plain');
                    if (userId) {
                        $wire.assignUserToUnit(userId, {{ $unit->id }});
                    }
                "
                class="relative flex items-center justify-between rounded-lg p-4 bg-gray-50 border-2 border-transparent transition-all duration-200 text-sm"
                :class="{ 'border-dashed border-blue-500 bg-blue-50': dragging, 'cursor-pointer': true }">
                <div class="flex items-center" @click="open = !open">
                    @if ($unit->children->count() > 0)
                        <span class="text-gray-500 text-xl mr-2 transform transition-transform duration-200"
                            :class="{ 'rotate-90': open }">▶</span>
                    @else
                        <span class="w-6 h-6 mr-2"></span>
                    @endif
                    <span class="font-semibold text-gray-800">{{ $unit->name }}</span>
                </div>
            </div>

            <ul x-show="open" x-collapse.duration.400ms class="mt-4 space-y-4">
                @if ($unit->users->count() > 0)
                    @foreach ($unit->users as $user)
                        <li wire:key="user-{{ $user->id }}"
                            class="pl-8 text-gray-600 bg-gray-200 rounded-md p-2 mb-2 cursor-grab text-sm transition-colors duration-200 hover:bg-gray-300"
                            draggable="true"
                            @dragstart.self="event.dataTransfer.setData('text/plain', {{ $user->id }});">
                            <span class="mr-2">👤</span>{{ $user->name }}
                        </li>
                    @endforeach
                @endif
                @include('partials.user-unit-tree', ['units' => $unit->children])
            </ul>
        </li>
    @endforeach
</ul>
