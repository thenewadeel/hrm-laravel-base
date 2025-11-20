<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Movement Report - {{ now()->format('Y-m-d') }}</title>
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
            max-width: 1200px;
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
        
        .period-info {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            text-align: center;
            font-weight: bold;
            color: {{ $theme['primary_color'] ?? '#2563eb' }};
        }
        
        .summary {
            padding: 15px 20px;
            background: #e9ecef;
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
            font-size: 16px;
            font-weight: bold;
            font-family: monospace;
        }
        
        .received { color: {{ $theme['success_color'] ?? '#10b981' }}; }
        .issued { color: {{ $theme['error_color'] ?? '#ef4444' }}; }
        .net { color: {{ $theme['primary_color'] ?? '#2563eb' }}; }
        
        .top-items {
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section h3 {
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #ddd;
            font-size: 16px;
        }
        
        .received h3 {
            color: {{ $theme['success_color'] ?? '#10b981' }};
            border-color: {{ $theme['success_color'] ?? '#10b981' }};
        }
        
        .issued h3 {
            color: {{ $theme['error_color'] ?? '#ef4444' }};
            border-color: {{ $theme['error_color'] ?? '#ef4444' }};
        }
        
        .movements-table {
            padding: 0 20px 20px 20px;
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
        
        .transaction-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .type-receipt { background: #d4edda; color: #155724; }
        .type-issue { background: #f8d7da; color: #721c24; }
        .type-transfer { background: #fff3cd; color: #856404; }
        
        .quantity {
            text-align: right;
            font-family: monospace;
            font-weight: bold;
        }
        
        .positive { color: {{ $theme['success_color'] ?? '#10b981' }}; }
        .negative { color: {{ $theme['error_color'] ?? '#ef4444' }}; }
        
        .date {
            white-space: nowrap;
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
            .summary { grid-template-columns: repeat(2, 1fr); }
            .top-items { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <h1>Stock Movement Report</h1>
            <div class="subtitle">{{ $brand['name'] ?? config('app.name') }}</div>
        </div>

        <!-- Period Info -->
        <div class="period-info">
            Period: {{ $filters['start_date'] }} to {{ $filters['end_date'] }}
            @if ($filters['transaction_type']) | Type: {{ $filters['transaction_type'] }} @endif
        </div>

        <!-- Summary Cards -->
        <div class="summary">
            <div class="summary-item">
                <div class="summary-label">Total Received</div>
                <div class="summary-value received">{{ number_format($summary['total_received'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Issued</div>
                <div class="summary-value issued">{{ number_format($summary['total_issued'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Net Movement</div>
                <div class="summary-value net">{{ number_format($summary['net_movement'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Transactions</div>
                <div class="summary-value">{{ $summary['total_transactions'] }}</div>
            </div>
        </div>

        <!-- Top Items Section -->
        @if (!empty($topReceived) || !empty($topIssued))
        <div class="top-items">
            @if (!empty($topReceived))
            <div class="section received">
                <h3>📈 Top Received Items</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Unit</th>
                            <th>Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topReceived as $item)
                        <tr>
                            <td><span class="item-code">{{ $item->sku ?? $item->code ?? 'N/A' }}</span></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="quantity positive">{{ number_format($item->total_quantity, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if (!empty($topIssued))
            <div class="section issued">
                <h3>📉 Top Issued Items</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Unit</th>
                            <th>Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topIssued as $item)
                        <tr>
                            <td><span class="item-code">{{ $item->sku ?? $item->code ?? 'N/A' }}</span></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="quantity negative">{{ number_format($item->total_quantity, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        <!-- Movements Table -->
        <div class="movements-table">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction</th>
                        <th>Type</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Store</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movements as $movement)
                    <tr>
                        <td class="date">{{ \Carbon\Carbon::parse($movement->transaction->transaction_date)->format('M d, Y') }}</td>
                        <td>{{ $movement->transaction->reference ?? 'N/A' }}</td>
                        <td><span class="transaction-type type-{{ $movement->transaction->type }}">{{ $movement->transaction->type }}</span></td>
                        <td><span class="item-code">{{ $movement->item->sku ?? $movement->item->code ?? 'N/A' }}</span></td>
                        <td>{{ $movement->item->name }}</td>
                        <td class="quantity {{ $movement->quantity >= 0 ? 'positive' : 'negative' }}">
                            {{ $movement->quantity >= 0 ? '' : '(' }}{{ number_format(abs($movement->quantity), 2) }}{{ $movement->quantity >= 0 ? '' : ')' }}
                        </td>
                        <td>{{ $movement->transaction->store->name ?? 'N/A' }}</td>
                        <td>{{ $movement->transaction->notes ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Movement Report for period {{ $filters['start_date'] }} to {{ $filters['end_date'] }}</p>
            <p>Generated on {{ now()->format('F d, Y H:i:s') }} by {{ $brand['name'] ?? config('app.name') }}</p>
        </div>
    </div>
</body>
</html>