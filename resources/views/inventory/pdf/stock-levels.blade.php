<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Levels Report - {{ now()->format('Y-m-d') }}</title>
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
        
        .summary {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            font-family: monospace;
        }
        
        .total-items { color: {{ $theme['primary_color'] ?? '#2563eb' }}; }
        .low-stock { color: {{ $theme['warning_color'] ?? '#f59e0b' }}; }
        .out-of-stock { color: {{ $theme['error_color'] ?? '#ef4444' }}; }
        .total-value { color: {{ $theme['success_color'] ?? '#10b981' }}; }
        
        .filters {
            padding: 10px 20px;
            background: #e9ecef;
            font-size: 11px;
            color: #666;
        }
        
        .items-table {
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .item-code {
            font-family: monospace;
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .quantity {
            text-align: right;
            font-family: monospace;
            font-weight: bold;
        }
        
        .cost {
            text-align: right;
            font-family: monospace;
        }
        
        .value {
            text-align: right;
            font-family: monospace;
            font-weight: bold;
        }
        
        .stock-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-out { background: #f8d7da; color: #721c24; }
        .status-low { background: #fff3cd; color: #856404; }
        .status-ok { background: #d4edda; color: #155724; }
        
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
            .summary { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <h1>Stock Levels Report</h1>
            <div class="preview">{{ $brand['name'] ?? config('app.name') }}</div>
        </div>

        <!-- Summary Cards -->
        <div class="summary">
            <div class="summary-item">
                <div class="summary-label">Total Items</div>
                <div class="summary-value total-items">{{ $summary['totalItems'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Low Stock</div>
                <div class="summary-value low-stock">{{ $summary['lowStockCount'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Out of Stock</div>
                <div class="summary-value out-of-stock">{{ $summary['outOfStockCount'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Value</div>
                <div class="summary-value total-value">${{ number_format($summary['totalValue'], 2) }}</div>
            </div>
        </div>

        <!-- Filters Info -->
        @if (!empty($filters))
        <div class="filters">
            <strong>Filters Applied:</strong>
            @if ($filters['store_id']) Store: {{ $filters['store_id'] }} @endif
            @if ($filters['category']) | Category: {{ $filters['category'] }} @endif
            @if ($filters['status']) | Status: {{ $filters['status'] }} @endif
        </div>
        @endif

        <!-- Items Table -->
        <div class="items-table">
            <table>
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Min Stock</th>
                        <th>Max Stock</th>
                        <th>Cost Price</th>
                        <th>Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                    <tr>
                        <td><span class="item-code">{{ $item['sku'] ?? $item['code'] ?? 'N/A' }}</span></td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['category'] }}</td>
                        <td class="quantity">{{ $item['total_quantity'] ?? 0 }}</td>
                        <td>{{ $item['min_stock'] }}</td>
                        <td>{{ $item['max_stock'] }}</td>
                        <td class="cost">${{ number_format($item['cost_price'] ?? 0, 2) }}</td>
                        <td class="value">${{ number_format(($item['cost_price'] ?? 0) * ($item['total_quantity'] ?? 0), 2) }}</td>
                        <td>
                            @if ($item['total_quantity'] <= 0)
                                <span class="stock-status status-out">OUT OF STOCK</span>
                            @elseif ($item['total_quantity'] <= $item['min_stock'])
                                <span class="stock-status status-low">LOW STOCK</span>
                            @else
                                <span class="stock-status status-ok">IN STOCK</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Stock Levels Report generated on {{ now()->format('F d, Y H:i:s') }} by {{ $brand['name'] ?? config('app.name') }}</p>
            <p>Total Items: {{ count($items) }} | Showing current inventory status</p>
        </div>
    </div>
</body>
</html>