<div class="flex h-screen bg-gray-100 p-8 font-sans">

    <div class="w-1/4 bg-white rounded-lg shadow-xl p-6 mr-6 flex flex-col">
        <h2 class="text-2xl font-bold mb-4">Unassigned Users</h2>
        <div class="mb-4">
            <input wire:model.live="search" type="search" placeholder="Search users..."
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex-grow overflow-y-auto pr-2">
            @forelse($unassignedUsers as $user)
                <div x-data="{ dragging: false }"
                    @dragstart.self="dragging = true; event.dataTransfer.setData('text/plain', {{ $user->id }});"
                    @dragend.self="dragging = false" draggable="true"
                    class="flex items-center bg-blue-50 text-blue-800 p-3 rounded-lg mb-2 cursor-grab text-sm transition-all duration-200 hover:bg-blue-100 transform hover:scale-105"
                    :class="{ 'opacity-50 transform scale-95': dragging }">
                    <span class="mr-2">👤</span>
                    {{ $user->name }} ({{ $user->email }})
                </div>
            @empty
                <div class="text-gray-500 text-center py-4 text-sm">
                    All users are assigned or no users match your search.
                </div>
            @endforelse
        </div>
    </div>

    <div class="w-3/4 bg-white rounded-lg shadow-xl p-6 flex flex-col">
        <h2 class="text-2xl font-bold mb-4">Organizational Structure</h2>
        <div class="flex-grow overflow-y-auto pr-2" x-data="{ dropTarget: false }" @dragover.prevent.stop="dropTarget = true"
            @dragleave.prevent.stop="dropTarget = false"
            @drop.prevent.stop="
                dropTarget = false;
                const userId = event.dataTransfer.getData('text/plain');
                if (userId) {
                    $wire.assignUserToUnit(userId, null); // Drop onto null unit
                }
            "
            :class="{ 'border-2 border-dashed border-gray-400': dropTarget }">
            <div class="min-h-[400px]">
                @include('partials.user-unit-tree', ['units' => $treeRoots])
            </div>
        </div>
    </div>
</div>
