<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance - {{ $asOfDate->format('Y-m-d') }}</title>
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
        
        .report-info {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .report-date {
            font-weight: bold;
            color: {{ $theme['primary_color'] ?? '#2563eb' }};
        }
        
        .balance-status {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .balanced {
            background: #d4edda;
            color: #155724;
        }
        
        .unbalanced {
            background: #f8d7da;
            color: #721c24;
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
        
        .account-code {
            font-family: monospace;
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
        }
        
        .account-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .type-asset { background: #d1ecf1; color: #0c5460; }
        .type-liability { background: #f8d7da; color: #721c24; }
        .type-equity { background: #d4edda; color: #155724; }
        .type-revenue { background: #d1ecf1; color: #0c5460; }
        .type-expense { background: #f8d7da; color: #721c24; }
        
        .amount {
            text-align: right;
            font-family: monospace;
            font-weight: bold;
        }
        
        .negative {
            color: #dc3545;
        }
        
        .positive {
            color: #28a745;
        }
        
        .totals {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .totals td {
            padding: 15px 12px;
            font-size: 13px;
            border-top: 2px solid {{ $theme['primary_color'] ?? '#2563eb' }};
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
            <h1>Trial Balance</h1>
            <div class="subtitle">{{ $brand['name'] ?? config('app.name') }}</div>
        </div>

        <!-- Report Info -->
        <div class="report-info">
            <div class="report-date">
                As of: {{ $asOfDate->format('F d, Y') }}
            </div>
            <div class="balance-status {{ $data['is_balanced'] ? 'balanced' : 'unbalanced' }}">
                {{ $data['is_balanced'] ? 'Balanced' : 'Unbalanced' }}
            </div>
        </div>

        <!-- Trial Balance Table -->
        <table>
            <thead>
                <tr>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th>Type</th>
                    <th>Debits</th>
                    <th>Credits</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['accounts'] as $account)
                <tr>
                    <td><span class="account-code">{{ $account['code'] }}</span></td>
                    <td>{{ $account['name'] }}</td>
                    <td><span class="account-type type-{{ $account['type'] }}">{{ $account['type'] }}</span></td>
                    <td class="amount">${{ number_format($account['debits'], 2) }}</td>
                    <td class="amount">${{ number_format($account['credits'], 2) }}</td>
                    <td class="amount {{ $account['balance'] >= 0 ? 'positive' : 'negative' }}">
                        ${{ number_format(abs($account['balance']), 2) }}
                        {{ $account['balance'] < 0 ? ' (CR)' : ' (DR)' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals">
                    <td colspan="3">TOTALS</td>
                    <td class="amount">${{ number_format($data['total_debits'], 2) }}</td>
                    <td class="amount">${{ number_format($data['total_credits'], 2) }}</td>
                    <td class="amount">
                        @if ($data['is_balanced'])
                            <span class="positive">Balanced</span>
                        @else
                            <span class="negative">
                                Difference: ${{ number_format(abs($data['total_debits'] - $data['total_credits']), 2) }}
                            </span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Generated on {{ now()->format('F d, Y H:i:s') }} by {{ $brand['name'] ?? config('app.name') }}</p>
            @if (!$data['is_balanced'])
            <p style="color: #dc3545; font-weight: bold;">⚠️ WARNING: Trial Balance is not balanced!</p>
            @endif
        </div>
    </div>
</body>
</html>