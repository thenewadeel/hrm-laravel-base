<div class="p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-semibold mb-4">Organizational Tree </h2>
    <div class="mb-4">
        <label for="organization-filter" class="block text-gray-700 font-bold mb-2">Filter by Organization:</label>
        <select id="organization-filter" wire:change="filterByOrganization($event.target.value)"
            class="block w-full border border-gray-300 rounded-md p-2">
            <option value="">All Organizations</option>
            @foreach (\App\Models\Organization::all() as $org)
                <option value="{{ $org->id }}">{{ $org->name }}</option>
            @endforeach
        </select>
    </div>
    <div x-data="{
        dropTarget: false,
        drop(event) {
            const unitId = event.dataTransfer.getData('text/plain');
            $wire.updateParent(unitId, null);
            this.dropTarget = false;
        },
        dragOver(event) {
            event.preventDefault();
            this.dropTarget = true;
        },
        dragLeave() {
            this.dropTarget = false;
        }
    }" @drop="drop" @dragover="dragOver" @dragleave="dragLeave"
        class="border-dashed p-4 rounded text-center mb-4 transition-colors"
        :class="{ 'border-blue-500 bg-blue-50': dropTarget, 'border-gray-300': !dropTarget }">
        <span class="text-gray-500">Drag a unit here to make it a root node.</span>
    </div>

    @if ($roots->count())
        <ul>
            @include('partials.organization-unit-tree', ['units' => $roots])
        </ul>
    @else
        <p class="text-gray-500">No organizational units found. Start by adding one!</p>
    @endif
</div>
