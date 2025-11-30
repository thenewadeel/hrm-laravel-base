# Badge System Documentation

## Overview

The HRM Laravel Base ERP system includes a comprehensive badge system designed for UI consistency and reusability. The badge system provides various components for displaying status indicators, categories, counts, and other visual information throughout the application.

## Components

### 1. Base Badge (`<x-ui-badge>`)

The foundational badge component with extensive customization options.

#### Props:
- `variant`: `'solid' | 'outline' | 'subtle'` (default: `'solid'`)
- `color`: `'gray' | 'red' | 'yellow' | 'green' | 'blue' | 'indigo' | 'purple' | 'pink'` (default: `'gray'`)
- `size`: `'xs' | 'sm' | 'md' | 'lg' | 'xl'` (default: `'md'`)
- `rounded`: `'none' | 'sm' | 'md' | 'lg' | 'full'` (default: `'full'`)
- `icon`: Icon name (optional)
- `iconPosition`: `'left' | 'right'` (default: `'left'`)
- `dismissible`: Boolean (default: `false`)
- `dot`: Boolean (default: `false`)

#### Examples:
```blade
<!-- Basic badge -->
<x-ui-badge>Default Badge</x-ui-badge>

<!-- Colored badge -->
<x-ui-badge color="green" variant="solid">Success</x-ui-badge>

<!-- Badge with icon -->
<x-ui-badge color="blue" icon="user">User</x-ui-badge>

<!-- Dismissible badge -->
<x-ui-badge color="red" dismissible>Removable</x-ui-badge>

<!-- Badge with dot indicator -->
<x-ui-badge color="green" dot>Active</x-ui-badge>
```

### 2. Status Badge (`<x-ui-status-badge>`)

Pre-configured status indicators for common application states.

#### Props:
- `status`: Status type (see supported statuses below)
- `size`: Badge size (default: `'md'`)
- `showIcon`: Boolean (default: `true`)
- `customLabel`: Custom label override

#### Supported Statuses:
- **General**: `active`, `inactive`, `pending`, `draft`
- **Financial**: `posted`, `unposted`, `void`, `reconciled`, `unreconciled`
- **HR**: `present`, `absent`, `leave`, `holiday`
- **Inventory**: `in_stock`, `low_stock`, `out_of_stock`, `discontinued`
- **System**: `online`, `offline`, `error`, `warning`, `success`, `info`
- **Financial Year**: `closing`, `closed`, `locked`, `unlocked`

#### Examples:
```blade
<x-ui-status-badge status="active" />
<x-ui-status-badge status="pending" />
<x-ui-status-badge status="error" />
<x-ui-status-badge status="posted" />
```

### 3. Category Badge (`<x-ui-category-badge>`)

Categorization badges for different types of data and operations.

#### Props:
- `category`: Category type (see supported categories below)
- `size`: Badge size (default: `'md'`)
- `showIcon`: Boolean (default: `true`)
- `customLabel`: Custom label override
- `clickable`: Boolean (default: `false`)

#### Supported Categories:
- **Accounting**: `asset`, `liability`, `equity`, `revenue`, `expense`
- **Transaction Types**: `sales`, `purchase`, `payment`, `receipt`, `journal`
- **HR**: `salary`, `allowance`, `deduction`, `bonus`, `commission`, `loan`, `advance`
- **Inventory**: `raw_material`, `finished_goods`, `consumable`, `service`
- **Priority**: `low`, `medium`, `high`, `critical`, `urgent`
- **Department**: `hr`, `finance`, `it`, `operations`, `sales`, `marketing`
- **Document Types**: `invoice`, `receipt`, `purchase_order`, `quotation`, `report`

#### Examples:
```blade
<x-ui-category-badge category="asset" />
<x-ui-category-badge category="sales" />
<x-ui-category-badge category="high" clickable />
```

### 4. Count Badge (`<x-ui-count-badge>`)

Notification-style count badges for displaying numbers.

#### Props:
- `count`: Number to display
- `max`: Maximum number before showing "N+" (default: `99`)
- `color`: Badge color (default: `'red'`)
- `size`: Badge size (default: `'sm'`)
- `showZero`: Boolean (default: `false`)
- `pulse`: Boolean for animation (default: `false`)
- `position`: `'top-right' | 'top-left' | 'bottom-right' | 'bottom-left'` (default: `'top-right'`)

#### Examples:
```blade
<div class="relative">
    <button>Notifications</button>
    <x-ui-count-badge count="5" />
</div>

<div class="relative">
    <button>Messages</button>
    <x-ui-count-badge count="150" max="99" pulse />
</div>
```

### 5. Key-Value Badge (`<x-ui-key-value-badge>`)

Badge with associated value display.

#### Props:
- `label`: Badge label
- `value`: Associated value
- `color`: Badge color (default: `'gray'`)
- `size`: Badge size (default: `'md'`)
- `variant`: Badge variant (default: `'solid'`)
- `showColon`: Boolean (default: `true`)

#### Examples:
```blade
<x-ui-key-value-badge label="Status" value="Active" color="green" />
<x-ui-key-value-badge label="Priority" value="High" color="red" />
```

### 6. Progress Badge (`<x-ui-progress-badge>`)

Progress indicator with badge styling.

#### Props:
- `progress`: Current progress value
- `max`: Maximum progress value (default: `100`)
- `color`: Progress color (default: `'blue'`)
- `size`: Badge size (default: `'md'`)
- `showPercentage`: Boolean (default: `true`)
- `animated`: Boolean for pulse animation (default: `false`)
- `variant`: Badge variant (default: `'solid'`)

#### Examples:
```blade
<x-ui-progress-badge progress="75" />
<x-ui-progress-badge progress="30" color="yellow" animated />
<x-ui-progress-badge progress="100" color="green" />
```

### 7. Tag Badges (`<x-ui-tag-badges>`)

Multiple tags display with optional removal and limiting.

#### Props:
- `tags`: Array of tags (strings or objects with 'label' and 'value')
- `color`: Badge color (default: `'blue'`)
- `size`: Badge size (default: `'sm'`)
- `variant`: Badge variant (default: `'outline'`)
- `removable`: Boolean (default: `false`)
- `limit`: Maximum number of tags to show (optional)
- `showMore`: Boolean for "N more" indicator (default: `true`)

#### Examples:
```blade
<x-ui-tag-badges :tags="['PHP', 'Laravel', 'Vue']" />
<x-ui-tag-badges :tags="$skills" removable limit="3" />
```

### 8. Role Badge (`<x-ui-role-badge>`)

User role and permission level indicators.

#### Props:
- `role`: Role type (see supported roles below)
- `size`: Badge size (default: `'md'`)
- `showIcon`: Boolean (default: `true`)
- `customLabel`: Custom label override

#### Supported Roles:
- **System**: `super_admin`, `admin`, `manager`, `supervisor`
- **HR**: `hr_manager`, `hr_specialist`, `recruiter`
- **Finance**: `accountant`, `finance_manager`, `auditor`
- **Employee**: `employee`, `senior_employee`, `lead`
- **Access Levels**: `full_access`, `limited_access`, `read_only`, `no_access`
- **Department Specific**: `sales_rep`, `support_agent`, `developer`, `designer`

#### Examples:
```blade
<x-ui-role-badge role="admin" />
<x-ui-role-badge role="hr_manager" />
<x-ui-role-badge role="developer" />
```

### 9. Priority Badge (`<x-ui-priority-badge>`)

Priority level indicators with optional icon-only mode.

#### Props:
- `priority`: Priority level (`low`, `medium`, `high`, `critical`, `urgent`, `emergency`)
- `size`: Badge size (default: `'md'`)
- `showIcon`: Boolean (default: `true`)
- `showLabel`: Boolean (default: `true`)
- `customLabel`: Custom label override

#### Examples:
```blade
<x-ui-priority-badge priority="high" />
<x-ui-priority-badge priority="critical" />
<x-ui-priority-badge priority="low" show-label="false" />
```

### 10. Type Badge (`<x-ui-type-badge>`)

Data type and file format indicators.

#### Props:
- `type`: Type identifier (see supported types below)
- `size`: Badge size (default: `'md'`)
- `showIcon`: Boolean (default: `true`)
- `showDot`: Boolean for dot indicator (default: `false`)
- `customLabel`: Custom label override

#### Supported Types:
- **Documents**: `pdf`, `excel`, `word`, `image`, `video`, `audio`, `archive`, `text`
- **Data Types**: `string`, `number`, `boolean`, `date`, `datetime`, `email`, `url`, `phone`, `currency`, `percentage`
- **API/Response**: `json`, `xml`, `html`, `css`, `javascript`
- **File Status**: `uploaded`, `downloading`, `processing`, `failed`

#### Examples:
```blade
<x-ui-type-badge type="pdf" />
<x-ui-type-badge type="json" />
<x-ui-type-badge type="email" />
```

## Color System

The badge system uses a consistent color palette:

- **Gray**: Neutral, inactive, or default states
- **Red**: Error, danger, critical, urgent situations
- **Yellow**: Warning, pending, caution states
- **Green**: Success, active, completed states
- **Blue**: Information, primary actions, neutral data
- **Indigo**: Secondary information, specific categories
- **Purple**: Special categories, premium features
- **Pink**: Creative or marketing-related items

## Size System

Consistent sizing across all badge components:

- **xs**: `px-1.5 py-0.5 text-xs` - Very compact
- **sm**: `px-2 py-0.5 text-xs` - Small, common for tables
- **md**: `px-2.5 py-0.5 text-sm` - Default size
- **lg**: `px-3 py-1 text-sm` - Larger, more prominent
- **xl**: `px-4 py-1.5 text-base` - Very large, headers

## Variant System

Three visual variants for different contexts:

- **Solid**: Filled background, high visibility
- **Outline**: Border only, subtle appearance
- **Subtle**: Light background, minimal visual weight

## Best Practices

1. **Consistency**: Use the same color for the same type of information throughout the application
2. **Accessibility**: Ensure sufficient color contrast and don't rely solely on color
3. **Icons**: Use icons to reinforce meaning, especially for status indicators
4. **Sizing**: Choose appropriate sizes based on context and hierarchy
5. **Variants**: Use subtle variants for secondary information, solid for primary
6. **Labels**: Always provide clear, concise labels
7. **Dark Mode**: All badges automatically support dark mode

## Migration from Old Components

To migrate from the old badge system:

```blade
<!-- Old -->
<x-badge color="green" size="md">Active</x-badge>

<!-- New -->
<x-ui-status-badge status="active" />

<!-- Old -->
<x-status-badge status="draft" />

<!-- New -->
<x-ui-status-badge status="draft" />
```

The new system provides better consistency, more options, and improved accessibility while maintaining backward compatibility through similar prop names.