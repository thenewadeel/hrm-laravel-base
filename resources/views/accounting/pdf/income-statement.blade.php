<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income Statement - {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</title>
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
            max-width: 900px;
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
        }
        
        .period-range {
            font-weight: bold;
            color: {{ $theme['primary_color'] ?? '#2563eb' }};
            font-size: 14px;
        }
        
        .sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 20px;
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
        
        .revenue h3 {
            color: {{ $theme['success_color'] ?? '#10b981' }};
            border-color: {{ $theme['success_color'] ?? '#10b981' }};
        }
        
        .expenses h3 {
            color: {{ $theme['error_color'] ?? '#ef4444' }};
            border-color: {{ $theme['error_color'] ?? '#ef4444' }};
        }
        
        .line-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .account-info {
            flex: 1;
        }
        
        .account-code {
            font-family: monospace;
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-right: 8px;
        }
        
        .amount {
            font-family: monospace;
            font-weight: bold;
            text-align: right;
            min-width: 100px;
        }
        
        .positive {
            color: {{ $theme['success_color'] ?? '#10b981' }};
        }
        
        .negative {
            color: {{ $theme['error_color'] ?? '#ef4444' }};
        }
        
        .total {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #ddd;
            font-weight: bold;
            font-size: 14px;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-top: 2px solid {{ $theme['primary_color'] ?? '#2563eb' }};
            margin: 20px 0 0 0;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .summary-amount {
            font-size: 18px;
            font-weight: bold;
            font-family: monospace;
        }
        
        .revenue-total { color: {{ $theme['success_color'] ?? '#10b981' }}; }
        .expense-total { color: {{ $theme['error_color'] ?? '#ef4444' }}; }
        .net-income { 
            color: {{ $data['net_income'] >= 0 ? ($theme['success_color'] ?? '#10b981') : ($theme['error_color'] ?? '#ef4444') }}; 
            font-size: 22px;
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
            .sections { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <h1>Income Statement</h1>
            <div class="subtitle">{{ $brand['name'] ?? config('app.name') }}</div>
        </div>

        <!-- Period Info -->
        <div class="period-info">
            <div class="period-range">
                Period: {{ $startDate->format('F d, Y') }} - {{ $endDate->format('F d, Y') }}
            </div>
        </div>

        <!-- Revenue and Expenses Sections -->
        <div class="sections">
            <!-- Revenue Section -->
            <div class="section revenue">
                <h3>REVENUE</h3>
                @foreach ($data['revenue'] as $account)
                <div class="line-item">
                    <div class="account-info">
                        <span class="account-code">{{ $account['code'] }}</span>
                        {{ $account['name'] }}
                    </div>
                    <div class="amount positive">
                        ${{ number_format($account['amount'], 2) }}
                    </div>
                </div>
                @endforeach
                
                <div class="line-item total">
                    <div class="account-info">Total Revenue</div>
                    <div class="amount positive revenue-total">
                        ${{ number_format($data['total_revenue'], 2) }}
                    </div>
                </div>
            </div>

            <!-- Expenses Section -->
            <div class="section expenses">
                <h3>EXPENSES</h3>
                @foreach ($data['expenses'] as $account)
                <div class="line-item">
                    <div class="account-info">
                        <span class="account-code">{{ $account['code'] }}</span>
                        {{ $account['name'] }}
                    </div>
                    <div class="amount negative">
                        (${{ number_format($account['amount'], 2) }})
                    </div>
                </div>
                @endforeach
                
                <div class="line-item total">
                    <div class="account-info">Total Expenses</div>
                    <div class="amount negative expense-total">
                        (${{ number_format($data['total_expenses'], 2) }})
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Revenue</div>
                    <div class="summary-amount revenue-total">
                        ${{ number_format($data['total_revenue'], 2) }}
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Expenses</div>
                    <div class="summary-amount expense-total">
                        ${{ number_format($data['total_expenses'], 2) }}
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Net Income</div>
                    <div class="summary-amount net-income">
                        @if ($data['net_income'] >= 0)
                            ${{ number_format($data['net_income'], 2) }}
                        @else
                            (${{ number_format(abs($data['net_income']), 2) }})
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Income Statement for period {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</p>
            <p>Generated on {{ now()->format('F d, Y H:i:s') }} by {{ $brand['name'] ?? config('app.name') }}</p>
        </div>
    </div>
</body>
</html>