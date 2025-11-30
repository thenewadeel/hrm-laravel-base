@props([
    'category',
    'size' => 'md',
    'showIcon' => true,
    'customLabel' => null,
    'clickable' => false,
])

@php
    $categoryConfig = [
        // Accounting Categories
        'asset' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'banknotes',
            'label' => 'Asset',
        ],
        'liability' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'credit-card',
            'label' => 'Liability',
        ],
        'equity' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'chart-bar',
            'label' => 'Equity',
        ],
        'revenue' => [
            'color' => 'emerald',
            'variant' => 'solid',
            'icon' => 'arrow-trending-up',
            'label' => 'Revenue',
        ],
        'expense' => [
            'color' => 'orange',
            'variant' => 'solid',
            'icon' => 'arrow-trending-down',
            'label' => 'Expense',
        ],
        
        // Transaction Types
        'sales' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'shopping-cart',
            'label' => 'Sales',
        ],
        'purchase' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'shopping-bag',
            'label' => 'Purchase',
        ],
        'payment' => [
            'color' => 'purple',
            'variant' => 'solid',
            'icon' => 'banknotes',
            'label' => 'Payment',
        ],
        'receipt' => [
            'color' => 'indigo',
            'variant' => 'solid',
            'icon' => 'receipt',
            'label' => 'Receipt',
        ],
        'journal' => [
            'color' => 'gray',
            'variant' => 'solid',
            'icon' => 'document-text',
            'label' => 'Journal',
        ],
        
        // HR Categories
        'salary' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'banknotes',
            'label' => 'Salary',
        ],
        'allowance' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'plus-circle',
            'label' => 'Allowance',
        ],
        'deduction' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'minus-circle',
            'label' => 'Deduction',
        ],
        'bonus' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'gift',
            'label' => 'Bonus',
        ],
        'commission' => [
            'color' => 'purple',
            'variant' => 'solid',
            'icon' => 'percent-badge',
            'label' => 'Commission',
        ],
        'loan' => [
            'color' => 'orange',
            'variant' => 'solid',
            'icon' => 'currency-dollar',
            'label' => 'Loan',
        ],
        'advance' => [
            'color' => 'indigo',
            'variant' => 'solid',
            'icon' => 'arrow-right-circle',
            'label' => 'Advance',
        ],
        
        // Inventory Categories
        'raw_material' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'cube',
            'label' => 'Raw Material',
        ],
        'finished_goods' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'package',
            'label' => 'Finished Goods',
        ],
        'consumable' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'wrench-screwdriver',
            'label' => 'Consumable',
        ],
        'service' => [
            'color' => 'purple',
            'variant' => 'solid',
            'icon' => 'cog',
            'label' => 'Service',
        ],
        
        // Priority Levels
        'low' => [
            'color' => 'gray',
            'variant' => 'subtle',
            'icon' => 'arrow-down',
            'label' => 'Low Priority',
        ],
        'medium' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'minus',
            'label' => 'Medium Priority',
        ],
        'high' => [
            'color' => 'orange',
            'variant' => 'solid',
            'icon' => 'arrow-up',
            'label' => 'High Priority',
        ],
        'critical' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'exclamation-triangle',
            'label' => 'Critical',
        ],
        'urgent' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'fire',
            'label' => 'Urgent',
        ],
        
        // Department Categories
        'hr' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'users',
            'label' => 'Human Resources',
        ],
        'finance' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'banknotes',
            'label' => 'Finance',
        ],
        'it' => [
            'color' => 'purple',
            'variant' => 'solid',
            'icon' => 'computer-desktop',
            'label' => 'IT',
        ],
        'operations' => [
            'color' => 'orange',
            'variant' => 'solid',
            'icon' => 'cog',
            'label' => 'Operations',
        ],
        'sales' => [
            'color' => 'emerald',
            'variant' => 'solid',
            'icon' => 'chart-bar',
            'label' => 'Sales',
        ],
        'marketing' => [
            'color' => 'pink',
            'variant' => 'solid',
            'icon' => 'megaphone',
            'label' => 'Marketing',
        ],
        
        // Document Types
        'invoice' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'document-text',
            'label' => 'Invoice',
        ],
        'receipt' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'receipt',
            'label' => 'Receipt',
        ],
        'purchase_order' => [
            'color' => 'purple',
            'variant' => 'solid',
            'icon' => 'document',
            'label' => 'Purchase Order',
        ],
        'quotation' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'currency-dollar',
            'label' => 'Quotation',
        ],
        'report' => [
            'color' => 'indigo',
            'variant' => 'solid',
            'icon' => 'chart-bar',
            'label' => 'Report',
        ],
    ];

    $config = $categoryConfig[$category] ?? [
        'color' => 'gray',
        'variant' => 'subtle',
        'icon' => 'tag',
        'label' => ucfirst($category),
    ];
@endphp

@if($clickable)
    <button {{ $attributes->merge(['class' => 'hover:scale-105 transition-transform duration-200']) }}>
        <x-new-badge 
            :color="$config['color']"
            :variant="$config['variant']"
            :size="$size"
            :icon="$showIcon ? $config['icon'] : null"
        >
            {{ $customLabel ?? $config['label'] }}
        </x-new-badge>
    </button>
@else
    <x-new-badge 
        :color="$config['color']"
        :variant="$config['variant']"
        :size="$size"
        :icon="$showIcon ? $config['icon'] : null"
        {{ $attributes }}
    >
        {{ $customLabel ?? $config['label'] }}
    </x-new-badge>
@endif