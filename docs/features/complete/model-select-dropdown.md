# Model Select Dropdown Component

An advanced, reusable dropdown component for selecting models with search functionality, loading states, and superior UX compared to standard HTML select elements.

## Features

- **Search Functionality**: Real-time filtering with support for multiple search terms
- **Multiple Selection**: Support for both single and multiple selection modes
- **Loading States**: Built-in loading indicators for async operations
- **Keyboard Navigation**: Full keyboard accessibility (Enter, Space, Escape, Arrow keys)
- **Responsive Design**: Mobile-friendly with touch support
- **Dark Mode**: Automatic dark mode support
- **Large Dataset Handling**: Efficient rendering with max visible items limit
- **Customizable**: Flexible sizing, styling, and behavior options

## Basic Usage

```blade
<x-model-select-dropdown
    name="employee_id"
    label="Select Employee"
    :options="$employees"
    placeholder="Choose an employee..."
/>
```

## Advanced Usage

### With Search and Multiple Selection

```blade
<x-model-select-dropdown
    name="selected_employees"
    label="Select Multiple Employees"
    :options="$employees"
    searchable
    multiple
    placeholder="Choose employees..."
    search-placeholder="Search by name or ID..."
    max-visible="5"
/>
```

### Required Field with Custom Size

```blade
<x-model-select-dropdown
    name="department"
    label="Department"
    :options="$departments"
    placeholder="Select department"
    required
    size="lg"
/>
```

## Options Format

The component expects an array of objects with the following structure:

```php
$options = [
    [
        'value' => 'emp_001',           // Required: The actual value
        'label' => 'John Doe',           // Required: Display text
        'description' => 'EMP001',       // Optional: Secondary text
        'searchTerms' => [               // Optional: Additional search terms
            'John',
            'Doe', 
            'EMP001',
            'john.doe@company.com'
        ]
    ],
    // ... more options
];
```

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `name` | string | - | **Required**. Input name for form submission |
| `label` | string | - | **Required**. Label text displayed above the dropdown |
| `value` | mixed | `null` | Currently selected value(s) |
| `options` | array | `[]` | Array of option objects (see format above) |
| `placeholder` | string | `'Select an option'` | Placeholder text when no option is selected |
| `searchable` | bool | `true` | Enable/disable search functionality |
| `multiple` | bool | `false` | Enable multiple selection mode |
| `size` | string | `'md'` | Component size: `'sm'`, `'md'`, or `'lg'` |
| `required` | bool | `false` | Mark field as required |
| `search-placeholder` | string | `'Search...'` | Placeholder text for search input |
| `max-visible` | int | `10` | Maximum number of options to display before "more" indicator |

## Livewire Integration

### Component Setup

```php
// In your Livewire component
class EmployeeManagement extends Component
{
    public $selectedEmployee = '';
    public $selectedEmployees = [];

    public function render()
    {
        $employees = Employee::where('organization_id', auth()->user()->current_organization_id)
            ->where('is_active', true)
            ->with('user')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'value' => $employee->id,
                    'label' => $employee->first_name . ' ' . $employee->last_name,
                    'description' => $employee->employee_id ?? null,
                    'searchTerms' => [
                        $employee->first_name,
                        $employee->last_name,
                        $employee->employee_id ?? '',
                        $employee->user->email ?? '',
                    ]
                ];
            })->toArray();

        return view('livewire.employee-management', [
            'employees' => $employees,
        ]);
    }
}
```

### Blade Template

```blade
<!-- Single Selection -->
<x-model-select-dropdown
    name="selected_employee"
    label="Select Employee"
    :value="$selectedEmployee"
    :options="$employees"
    searchable
    required
/>

<!-- Multiple Selection -->
<x-model-select-dropdown
    name="selected_employees"
    label="Select Multiple Employees"
    :value="$selectedEmployees"
    :options="$employees"
    searchable
    multiple
    max-visible="8"
/>
```

## Data Handling

### Single Selection
- The component submits a single value as a string/integer
- In Livewire: `$selectedEmployee` contains the selected employee ID

### Multiple Selection
- The component submits a JSON array of selected values
- In Livewire: `$selectedEmployees` contains an array of selected employee IDs

## Styling and Customization

The component uses Tailwind CSS classes and supports:

- **Dark Mode**: Automatic dark mode detection and styling
- **Responsive**: Mobile-friendly design with touch support
- **Custom Sizes**: Small, medium, and large variants
- **Focus States**: Consistent focus styling with the rest of your application

## Accessibility

- **ARIA Attributes**: Proper ARIA labels and roles for screen readers
- **Keyboard Navigation**: Full keyboard support (Tab, Enter, Space, Escape, Arrow keys)
- **Focus Management**: Proper focus handling and visual indicators
- **Screen Reader Support**: Descriptive labels and state announcements

## Performance Considerations

- **Virtual Scrolling**: Large datasets are handled with max-visible limit
- **Efficient Filtering**: Client-side search with debounced input
- **Minimal Re-renders**: Alpine.js reactive updates only when necessary
- **Memory Efficient**: Options are filtered client-side without server requests

## Migration from Standard Select

### Before
```blade
<select name="employee_id" class="w-full rounded-md border-gray-300">
    <option value="">Select Employee</option>
    @foreach($employees as $employee)
        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
    @endforeach
</select>
```

### After
```blade
@php
    $employeeOptions = $employees->map(function ($employee) {
        return [
            'value' => $employee->id,
            'label' => $employee->first_name . ' ' . $employee->last_name,
            'description' => $employee->employee_id ?? null,
            'searchTerms' => [$employee->first_name, $employee->last_name, $employee->employee_id ?? '']
        ];
    })->toArray();
@endphp

<x-model-select-dropdown
    name="employee_id"
    label="Select Employee"
    :options="$employeeOptions"
    searchable
    placeholder="Choose an employee..."
/>
```

## Demo

Visit `/demo/model-select` to see the component in action with various configurations and real data from your application.

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Dependencies

- **Alpine.js**: Included with Livewire 3
- **Tailwind CSS**: For styling
- **Laravel Blade**: Component system