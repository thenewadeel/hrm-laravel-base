# Dropdown Components Documentation

## Overview
The dropdown system has been consolidated to eliminate confusion and circular references. There are now two distinct dropdown components:

## Components

### 1. Standalone Dropdown Component
**File:** `/resources/views/components/dropdown.blade.php`
**Usage:** `<x-dropdown>`

Used for general-purpose dropdowns throughout the application. Uses `$trigger` and `$content` parameters.

```blade
<x-dropdown>
    <x-slot name="trigger">
        <button>Click me</button>
    </x-slot>
    <x-slot name="content">
        <a href="#">Link 1</a>
        <a href="#">Link 2</a>
    </x-slot>
</x-dropdown>
```

### 2. Navigation Dropdown Component
**File:** `/resources/views/components/navigation/dropdown.blade.php`
**Usage:** `<x-navigation.dropdown>`

Used specifically within navigation components. Uses `$trigger` slot and `$slot` for content.

```blade
<x-navigation.dropdown align="left" width="56">
    <x-slot name="trigger">
        <x-navigation.link href="#" icon="📦">
            Inventory
        </x-navigation.link>
    </x-slot>
    
    <x-navigation.dropdown-link href="{{ route('inventory.items.index') }}" icon="📦">
        Items
    </x-navigation.dropdown-link>
</x-navigation.dropdown>
```

### 3. Navigation Dropdown Link Component
**File:** `/resources/views/components/navigation/dropdown-link.blade.php`
**Usage:** `<x-navigation.dropdown-link>`

Used for individual links within navigation dropdowns.

```blade
<x-navigation.dropdown-link href="{{ route('items.index') }}" icon="📦">
    Items
</x-navigation.dropdown-link>
```

## Removed Components

### ❌ Dropdown Container (REMOVED)
**File:** `/resources/views/components/navigation/dropdown-container.blade.php`

This component was causing circular references and has been removed. All usage has been migrated to use `<x-navigation.dropdown>`.

## Migration Summary

- **Before:** Confusing circular reference between `dropdown-container` and `dropdown`
- **After:** Clean separation between standalone dropdown and navigation-specific dropdown
- **Result:** No more memory dumps or confusion

## Usage Patterns

### Navigation Menus
Use `<x-navigation.dropdown>` for all navigation dropdowns:

```blade
<x-navigation.dropdown align="right" width="48">
    <x-slot name="trigger">
        <button>Menu</button>
    </x-slot>
    <x-navigation.dropdown-link href="/profile">Profile</x-navigation.dropdown-link>
    <x-navigation.dropdown-link href="/settings">Settings</x-navigation.dropdown-link>
</x-navigation.dropdown>
```

### General Dropdowns
Use `<x-dropdown>` for non-navigation dropdowns:

```blade
<x-dropdown align="left" width="60">
    <x-slot name="trigger">
        <button>Actions</button>
    </x-slot>
    <x-slot name="content">
        <a href="/edit">Edit</a>
        <a href="/delete">Delete</a>
    </x-slot>
</x-dropdown>
```

## Features

### Supported Alignments
- `left` (default for navigation)
- `right` (default for standalone)
- `top`

### Supported Widths
- `48` (default)
- `56`
- `60`
- `64`
- `80`

### Dark Mode Support
All components support dark mode through appropriate Tailwind classes.

### Transitions
All dropdowns include smooth open/close transitions using Alpine.js.

## Files Updated

The following files were updated to use the unified dropdown system:

1. `/resources/views/components/navigation/main.blade.php`
2. `/resources/views/components/navigation/examples.blade.php`
3. `/resources/views/components/navigation/desktop-menu.blade.php`
4. `/resources/views/components/navigation/portal-desktop-menu.blade.php`

## Testing

All dropdown components have been tested and verified to work without memory issues or circular references.