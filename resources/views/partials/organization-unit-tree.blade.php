@foreach ($units as $unit)
    <li wire:key="unit-{{ $unit->id }}" class="relative">
        <div x-data="{
            dropTarget: false,
            drop(event) {
                event.preventDefault();
                const unitId = event.dataTransfer.getData('text/plain');
                $wire.updateParent(unitId, {{ $unit->id }});
                this.dropTarget = false;
            },
            dragOver(event) {
                event.preventDefault();
                this.dropTarget = true;
            },
            dragLeave() {
                this.dropTarget = false;
            },
        }" @drop="drop" @dragover="dragOver" @dragleave="dragLeave"
            class="flex items-center pl-4 py-2 border-l border-r-4 border-dashed border-transparent transition-colors duration-200"
            :class="{ 'border-blue-500 bg-blue-50': dropTarget }">
            <div class="flex items-center cursor-move" draggable="true" x-data="{
                dragStart(event) {
                    event.dataTransfer.setData('text/plain', {{ $unit->id }});
                    event.stopPropagation();
                }
            }" @dragstart="dragStart">
                <span class="mr-2 text-gray-500">▶</span>
                {{ $unit->name }}
            </div>
        </div>

        <div x-data="{
            dropTarget: false,
            drop(event) {
                event.preventDefault();
                const unitId = event.dataTransfer.getData('text/plain');
                const parentId = {{ $unit->parent_id ?? 'null' }};
                $wire.updateParent(unitId, parentId);
                this.dropTarget = false;
            },
            dragOver(event) {
                event.preventDefault();
                this.dropTarget = true;
            },
            dragLeave() {
                this.dropTarget = false;
            },
        }" @drop="drop" @dragover="dragOver" @dragleave="dragLeave"
            class="h-2 w-full ml-4 border-b-2 border-transparent transition-colors duration-200"
            :class="{ 'border-blue-500': dropTarget }"></div>

        @if ($unit->children->count())
            <ul class="mt-2 ml-4">
                @include('partials.organization-unit-tree', ['units' => $unit->children])
            </ul>
        @endif
    </li>
@endforeach
