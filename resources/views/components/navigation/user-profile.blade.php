@props([
    'user' => null,
    'showRole' => true,
    'showStatus' => true,
    'showAvatar' => true,
    'size' => 'md', // sm, md, lg
])

@php
$sizeClasses = [
    'sm' => 'h-6 w-6 text-xs',
    'md' => 'h-8 w-8 text-sm',
    'lg' => 'h-10 w-10 text-base',
];

$statusColors = [
    'online' => 'bg-green-400',
    'away' => 'bg-yellow-400',
    'offline' => 'bg-gray-400',
    'busy' => 'bg-red-400',
];

$classes = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div class="flex items-center space-x-3">
    @if($showAvatar && $user)
        <div class="relative">
            <img class="rounded-full object-cover {{ $classes }}" 
                 src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF' }}" 
                 alt="{{ $user->name }}">
            
            @if($showStatus && isset($user->status))
                <span class="absolute bottom-0 right-0 block h-2 w-2 rounded-full {{ $statusColors[$user->status] ?? $statusColors['online'] }} ring-2 ring-white"></span>
            @endif
        </div>
    @endif
    
    <div class="flex-1 min-w-0">
        @if($user)
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                {{ $user->name }}
            </p>
            
            @if($showRole && isset($user->current_role))
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ $user->current_role }}
                </p>
            @endif
        @else
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Guest User
            </p>
        @endif
    </div>
</div>