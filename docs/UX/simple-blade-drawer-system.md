# Simple Blade Drawer System Specification
**HRM Laravel Base ERP System**  
*Created: December 14, 2025*

## Overview

A simple, elegant drawer system built entirely with Blade components and Alpine.js, using slots for content passing. No complex JavaScript state management - just clean, declarative Blade components.

## Architecture

### Core Components

#### 1. `<x-drawer-container>`
The main wrapper that manages all drawers and provides the layout structure.

```blade
<x-drawer-container>
    <!-- Main content goes here -->
    {{ $slot }}
</x-drawer-container>
```

**Features:**
- Sets up the main layout grid
- Provides drawer positioning context
- Handles responsive behavior
- Manages z-index layering

#### 2. `<x-drawer>`
Individual drawer component with configurable position and content.

```blade
<x-drawer position="left" title="Navigation" :open="$leftDrawerOpen">
    <x-drawer.toggle target="left-drawer" />
    
    <x-slot name="content">
        <!-- Drawer content here -->
        <nav>
            <!-- Navigation items -->
        </nav>
    </x-slot>
</x-drawer>
```

**Attributes:**
- `position`: left|right|top|bottom
- `title`: Drawer title (optional)
- `open`: Boolean state (managed by Alpine)
- `width`: Custom width (left/right only)
- `height`: Custom height (top/bottom only)

#### 3. `<x-drawer.toggle>`
Button to toggle drawer open/closed state.

```blade
<x-drawer.toggle target="left-drawer" class="custom-class">
    <!-- Button content -->
    <svg>...</svg>
</x-drawer.toggle>
```

**Attributes:**
- `target`: Drawer ID to toggle
- `class`: Additional CSS classes

## Implementation Details

### 1. Blade Component Structure

```
resources/views/components/drawer/
├── container.blade.php
├── drawer.blade.php
├── toggle.blade.php
└── overlay.blade.php
```

### 2. Alpine.js State Management

Simple Alpine.js data stored on the container:

```javascript
x-data="{
    drawers: {
        left: false,
        right: false,
        top: false,
        bottom: false
    },
    toggleDrawer(position) {
        this.drawers[position] = !this.drawers[position];
    },
    closeDrawer(position) {
        this.drawers[position] = false;
    },
    closeAllDrawers() {
        Object.keys(this.drawers).forEach(key => {
            this.drawers[key] = false;
        });
    }
}"
```

### 3. Slot-Based Content System

#### Left Drawer - App Navigation
```blade
<x-drawer position="left" title="Navigation" :open="$drawers.left">
    <x-slot name="content">
        <x-drawer-navigation />
    </x-slot>
</x-drawer>
```

#### Right Drawer - Module Settings
```blade
<x-drawer position="right" title="Settings" :open="$drawers.right">
    <x-slot name="content">
        <x-drawer-module-settings :module="$currentModule" />
    </x-slot>
</x-drawer>
```

#### Top Drawer - App Information
```blade
<x-drawer position="top" title="Information" :open="$drawers.top">
    <x-slot name="content">
        <x-drawer-app-info />
    </x-slot>
</x-drawer>
```

#### Bottom Drawer - User Preferences
```blade
<x-drawer position="bottom" title="Preferences" :open="$drawers.bottom">
    <x-slot name="content">
        <x-drawer-user-preferences :user="auth()->user()" />
    </x-slot>
</x-drawer>
```

## Usage Examples

### Basic Usage
```blade
<x-drawer-container>
    <!-- Main Content -->
    <main class="flex-1">
        <header>
            <x-drawer.toggle target="left-drawer" class="mr-4">
                <svg class="w-6 h-6">...</svg>
            </x-drawer.toggle>
            
            <h1>Page Title</h1>
        </header>
        
        <div>
            {{ $slot }}
        </div>
    </main>
    
    <!-- Left Navigation Drawer -->
    <x-drawer position="left" title="Navigation">
        <x-slot name="content">
            <nav class="p-4">
                <x-navigation.link href="/">Dashboard</x-navigation.link>
                <x-navigation.link href="/inventory">Inventory</x-navigation.link>
                <x-navigation.link href="/accounting">Accounting</x-navigation.link>
            </nav>
        </x-slot>
    </x-drawer>
    
    <!-- Right Settings Drawer -->
    <x-drawer position="right" title="Settings">
        <x-slot name="content">
            <div class="p-4">
                <h3>Module Settings</h3>
                <!-- Module-specific settings -->
            </div>
        </x-slot>
    </x-drawer>
</x-drawer-container>
```

### Advanced Usage with Context
```blade
<x-drawer-container>
    <!-- Main Content with Context -->
    <main class="flex-1">
        @yield('content')
    </main>
    
    <!-- Left Drawer - App Navigation -->
    <x-drawer position="left" title="Navigation">
        <x-slot name="content">
            <x-drawer.app-navigation />
        </x-slot>
    </x-drawer>
    
    <!-- Right Drawer - Module Settings (Contextual) -->
    <x-drawer position="right" title="{{ $moduleSettingsTitle ?? 'Settings' }}">
        <x-slot name="content">
            @if(request()->routeIs('inventory.*'))
                <x-drawer.inventory-settings />
            @elseif(request()->routeIs('accounting.*'))
                <x-drawer.accounting-settings />
            @elseif(request()->routeIs('hr.*'))
                <x-drawer.hr-settings />
            @else
                <x-drawer.general-settings />
            @endif
        </x-slot>
    </x-drawer>
    
    <!-- Top Drawer - App Information -->
    <x-drawer position="top" title="App Information">
        <x-slot name="content">
            <x-drawer.app-info />
        </x-slot>
    </x-drawer>
    
    <!-- Bottom Drawer - User Preferences -->
    <x-drawer position="bottom" title="User Preferences">
        <x-slot name="content">
            <x-drawer.user-preferences />
        </x-slot>
    </x-drawer>
</x-drawer-container>
```

## Styling and Design

### CSS Classes Used
```css
/* Container */
.drawer-container {
    @apply relative grid grid-cols-[1fr] grid-rows-[1fr];
}

/* Drawer Positions */
.drawer-left {
    @apply fixed left-0 top-0 h-full w-80 transform -translate-x-full transition-transform duration-300 ease-in-out;
}

.drawer-right {
    @apply fixed right-0 top-0 h-full w-80 transform translate-x-full transition-transform duration-300 ease-in-out;
}

.drawer-top {
    @apply fixed top-0 left-0 w-full h-80 transform -translate-y-full transition-transform duration-300 ease-in-out;
}

.drawer-bottom {
    @apply fixed bottom-0 left-0 w-full h-80 transform translate-y-full transition-transform duration-300 ease-in-out;
}

/* Open States */
.drawer-left.open {
    @apply translate-x-0;
}

.drawer-right.open {
    @apply translate-x-0;
}

.drawer-top.open {
    @apply translate-y-0;
}

.drawer-bottom.open {
    @apply translate-y-0;
}

/* Overlay */
.drawer-overlay {
    @apply fixed inset-0 bg-black bg-opacity-50 z-40;
}

/* Responsive Behavior */
@media (max-width: 768px) {
    .drawer-left,
    .drawer-right {
        @apply w-full;
    }
    
    .drawer-top,
    .drawer-bottom {
        @apply h-64;
    }
}
```

## Accessibility Features

### ARIA Attributes
```blade
<div x-data="drawerState" 
     x-bind:aria-hidden="!drawers.left"
     x-bind:aria-label="title + ' drawer'"
     role="region">
```

### Keyboard Navigation
- `ESC` key closes all open drawers
- `Tab` key navigates through drawer content
- Focus trapping when drawer is open

### Screen Reader Support
- Proper ARIA labels and roles
- Live regions for drawer state announcements
- Semantic HTML structure

## Integration with Existing Components

### Replacing Current Layout
- Replace `app-layout.blade.php` with drawer-based layout
- Migrate `navigation-main.blade.php` content to left drawer
- Move user profile to bottom drawer
- Add module settings to right drawer

### Backward Compatibility
- Keep existing layout components as fallback
- Use feature flags for gradual rollout
- Maintain current navigation patterns

## Performance Considerations

### Lazy Loading
- Drawer content loads only when opened
- Use `x-show` with `x-transition` for smooth animations
- Minimal JavaScript footprint

### Optimization
- CSS-only animations where possible
- Efficient Alpine.js state management
- Minimal re-renders with proper Alpine directives

## Testing Strategy

### Component Tests
- Test drawer open/close functionality
- Verify slot content rendering
- Test responsive behavior
- Validate accessibility features

### Integration Tests
- Test with existing navigation components
- Verify module context switching
- Test user preference persistence

### User Acceptance Tests
- First-time user experience
- Power user efficiency
- Mobile usability
- Accessibility compliance

## Migration Path

### Phase 1: Core Components
1. Create drawer container and base drawer component
2. Implement toggle functionality
3. Add basic styling and animations

### Phase 2: Content Integration
1. Migrate navigation to left drawer
2. Create module settings for right drawer
3. Implement app info and user preferences

### Phase 3: Enhancement
1. Add responsive behavior
2. Implement accessibility features
3. Add animation polish
4. Performance optimization

This simple approach provides a clean, maintainable drawer system using Blade's slot system and minimal JavaScript, making it easy to understand, customize, and maintain.