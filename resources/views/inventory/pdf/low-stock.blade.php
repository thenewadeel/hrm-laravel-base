<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Low Stock Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        
        .report-container {
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid #ddd;
            background: #fff;
        }
        
        .header {
            background: {{ $theme['primary_color'] ?? '#2563eb' }};
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .filters {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
            color: #666;
        }
        
        .section {
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .section h3 {
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #ddd;
            font-size: 16px;
        }
        
        .critical h3 {
            color: {{ $theme['error_color'] ?? '#ef4444' }};
            border-color: {{ $theme['error_color'] ?? '#ef4444' }};
        }
        
        .warning h3 {
            color: {{ $theme['warning_color'] ?? '#f59e0b' }};
            border-color: {{ $theme['warning_color'] ?? '#f59e0b' }};
        }
        
        .suggestions h3 {
            color: {{ $theme['success_color'] ?? '#10b981' }};
            border-color: {{ $theme['success_color'] ?? '#10b981' }};
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .item-code {
            font-family: monospace;
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        
        .stock-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-out { background: #f8d7da; color: #721c24; }
        .status-low { background: #fff3cd; color: #856404; }
        .status-ok { background: #d4edda; color: #155724; }
        
        .quantity {
            text-align: right;
            font-family: monospace;
            font-weight: bold;
        }
        
        .low-quantity { color: {{ $theme['warning_color'] ?? '#f59e0b' }}; }
        .out-quantity { color: {{ $theme['error_color'] ?? '#ef4444' }}; }
        
        .suggestion-amount {
            text-align: right;
            font-family: monospace;
            font-weight: bold;
            color: {{ $theme['success_color'] ?? '#10b981' }};
        }
        
        .footer {
            padding: 15px 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        
        @media print {
            body { margin: 0; padding: 10px; }
            .report-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <h1>Low Stock Report</h1>
            <div class="subtitle">{{ $brand['name'] ?? config('app.name') }}</div>
        </div>

        <!-- Filters Info -->
        @if (!empty($filters))
        <div class="filters">
            <strong>Filters Applied:</strong>
            @if ($filters['store_id']) Store: {{ $filters['store_id'] }} @endif
            @if ($filters['category']) | Category: {{ $filters['category'] }} @endif
            @if ($filters['severity']) | Severity: {{ $filters['severity'] }} @endif
        </div>
        @endif

        <!-- Out of Stock Section -->
        @if (!empty($outOfStockItems))
        <div class="section critical">
            <h3>🚨 OUT OF STOCK ITEMS</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($outOfStockItems as $item)
                    <tr>
                        <td><span class="item-code">{{ $item['sku'] ?? $item['code'] ?? 'N/A' }}</span></td>
                        <td>{{ $item['name'] }}</td>
                        <td class="quantity out-quantity">{{ $item['total_quantity'] ?? 0 }}</td>
                        <td>{{ $item['reorder_level'] }}</td>
                        <td><span class="stock-status status-out">OUT OF STOCK</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Low Stock Section -->
        @if (!empty($lowStockItems))
        <div class="section warning">
            <h3>⚠️ LOW STOCK ITEMS</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lowStockItems as $item)
                    <tr>
                        <td><span class="item-code">{{ $item['sku'] ?? $item['code'] ?? 'N/A' }}</span></td>
                        <td>{{ $item['name'] }}</td>
                        <td class="quantity low-quantity">{{ $item['total_quantity'] ?? 0 }}</td>
                        <td>{{ $item['reorder_level'] }}</td>
                        <td><span class="stock-status status-low">LOW STOCK</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Reorder Suggestions Section -->
        @if (!empty($reorderSuggestions))
        <div class="section suggestions">
            <h3>📋 REORDER SUGGESTIONS</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Suggested Order</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reorderSuggestions as $suggestion)
                    <tr>
                        <td><span class="item-code">{{ $suggestion['item']['sku'] ?? $suggestion['item']['code'] ?? 'N/A' }}</span></td>
                        <td>{{ $suggestion['item']['name'] }}</td>
                        <td class="quantity">{{ $suggestion['current'] }}</td>
                        <td>{{ $suggestion['reorder_level'] }}</td>
                        <td class="suggestion-amount">{{ $suggestion['suggested_order'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Low Stock Report generated on {{ now()->format('F d, Y H:i:s') }} by {{ $brand['name'] ?? config('app.name') }}</p>
            @if (empty($outOfStockItems) && empty($lowStockItems))
            <p>✅ All items are adequately stocked!</p>
            @endif
        </div>
    </div>
</body>
</html>