@props(['position' => 'top-right'])<div x-data="{
    notifications: [{
        id: Date.now() + Math.random(),
        type: 'success',
        message: 'Welcome to our application!',
        show: true
    }, {
        id: Date.now() + Math.random(),
        type: 'error',
        message: 'Welcome to our application!',
        show: true
    }, {
        id: Date.now() + Math.random(),
        type: 'info',
        message: 'Welcome to our application!',
        show: true
    }, {
        id: Date.now() + Math.random(),
        type: 'warning',
        message: 'Welcome to our application!',
        show: true
    }, {
        id: Date.now() + Math.random(),
        message: 'Welcome to our application!',
        show: true
    }],
    addNotification(type, message, event) {
        {{-- console.log('Type:' + type + ' Message:' + message + ' Event:'); --}}
        {{-- console.log({ event }); --}}
        const id = Date.now() + Math.random();
        this.notifications.unshift({ id, type, message, show: false });
        this.$nextTick(() => { const addedNotification = this.notifications.find(n => n.id === id); if (addedNotification) { addedNotification.show = true; } });
        setTimeout(() => this.hideNotification(id), 5000);
    },
    hideNotification(id) {
        const notification = this.notifications.find(n => n.id === id);
        if (notification) {
            notification.show = false;
        }
        setTimeout(() => this.removeNotification(id), 200);
    },
    removeNotification(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    }
}"
    @notify.window="addNotification($event.detail[0].type, $event.detail[0].message,$event)"class="fixed z-50 {{ $position === 'top-right' ? 'top-4 right-4' : ($position === 'top-left' ? 'top-4 left-4' : ($position === 'bottom-right' ? 'bottom-4 right-4' : 'bottom-4 left-4')) }} space-y-3">
    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 scale-95"
            :class="{
                'border-green-500': notification.type === 'success',
                'border-red-500': notification.type === 'error',
                'border-blue-500': notification.type === 'info',
                'border-yellow-500': notification.type === 'warning',
                'border-gray-500': notification.type !== 'success' && notification.type !== 'error' && notification
                    .type !== 'info' && notification.type !== 'warning'
            }"
            class="flex items-center p-2 rounded-lg shadow-slate-500 text-sky-950 min-w-[20rem] max-w-sm bg-gray-100 border-8 shadow-inner">

            <div class="flex-shrink-0 mr-3 ">
                <template x-if="notification.type === 'success'">
                    <x-heroicon-s-check-circle class="h-5 w-5" />
                </template>
                <template x-if="notification.type === 'error'">
                    <x-heroicon-s-x-circle class="h-5 w-5" />
                </template>
                <template x-if="notification.type === 'info'">
                    <x-heroicon-s-information-circle class="h-5 w-5" />
                </template>
                <template x-if="notification.type === 'warning'">
                    <x-heroicon-s-exclamation-triangle class="h-5 w-5" />
                </template>
            </div>

            <div class="flex-1">
                <p x-text="notification.message" class="text-sm font-medium"></p>
            </div>

            <button @click.stop="hideNotification(notification.id)"
                class="ml-2 p-1 rounded hover:bg-white hover:bg-opacity-20">
                <x-heroicon-s-x-mark class="h-4 w-4" />
            </button>
        </div>
    </template>
</div>
