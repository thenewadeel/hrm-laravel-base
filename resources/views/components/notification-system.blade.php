@props(['position' => 'top-right']) <!-- top-right, top-left, bottom-right, bottom-left -->

<div x-data="{
    notifications: [],
    addNotification(type, message) {
        const id = Date.now() + Math.random();
        this.notifications.push({ id, type, message });
        setTimeout(() => this.removeNotification(id), 5000);
    },
    removeNotification(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    }
}" @notify.window="addNotification($event.detail.type, $event.detail.message)"
    class="fixed z-50 {{ $position === 'top-right'
        ? 'top-4 right-4'
        : ($position === 'top-left'
            ? 'top-4 left-4'
            : ($position === 'bottom-right'
                ? 'bottom-4 right-4'
                : 'bottom-4 left-4')) }} space-y-2">
    <template x-for="notification in notifications" :key="notification.id">
        <div x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            :class="{
                'bg-green-500': notification.type === 'success',
                'bg-red-500': notification.type === 'error',
                'bg-blue-500': notification.type === 'info',
                'bg-yellow-500': notification.type === 'warning'
            }"
            class="text-white px-6 py-3 rounded-lg shadow-lg cursor-pointer min-w-64"
            @click="removeNotification(notification.id)">
            <div class="flex items-center justify-between">
                <p x-text="notification.message" class="text-sm font-medium"></p>
                <button @click="removeNotification(notification.id)" class="ml-4 hover:opacity-75">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>
