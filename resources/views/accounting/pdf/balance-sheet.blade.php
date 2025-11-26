<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet - {{ $asOfDate->format('Y-m-d') }}</title>
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
        
        .balance-sheet {
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
        
        .assets h3 {
            color: {{ $theme['primary_color'] ?? '#2563eb' }};
            border-color: {{ $theme['primary_color'] ?? '#2563eb' }};
        }
        
        .liabilities h3 {
            color: {{ $theme['warning_color'] ?? '#f59e0b' }};
            border-color: {{ $theme['warning_color'] ?? '#f59e0b' }};
        }
        
        .equity h3 {
            color: {{ $theme['success_color'] ?? '#10b981' }};
            border-color: {{ $theme['success_color'] ?? '#10b981' }};
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
        
        .assets-total { color: {{ $theme['primary_color'] ?? '#2563eb' }}; }
        .liabilities-total { color: {{ $theme['warning_color'] ?? '#f59e0b' }}; }
        .equity-total { color: {{ $theme['success_color'] ?? '#10b981' }}; }
        
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
            .balance-sheet { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <h1>Balance Sheet</h1>
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

        <!-- Balance Sheet Sections -->
        <div class="balance-sheet">
            <!-- Assets Section -->
            <div class="section assets">
                <h3>ASSETS</h3>
                @foreach ($data['assets'] as $account)
                <div class="line-item">
                    <div class="account-info">
                        <span class="account-code">{{ $account['code'] }}</span>
                        {{ $account['name'] }}
                    </div>
                    <div class="amount">
                        ${{ number_format($account['balance'], 2) }}
                    </div>
                </div>
                @endforeach
                
                <div class="line-item total">
                    <div class="account-info">Total Assets</div>
                    <div class="amount assets-total">
                        ${{ number_format($data['total_assets'], 2) }}
                    </div>
                </div>
            </div>

            <!-- Liabilities and Equity Section -->
            <div>
                <!-- Liabilities Section -->
                <div class="section liabilities">
                    <h3>LIABILITIES</h3>
                    @foreach ($data['liabilities'] as $account)
                    <div class="line-item">
                        <div class="account-info">
                            <span class="account-code">{{ $account['code'] }}</span>
                            {{ $account['name'] }}
                        </div>
                        <div class="amount">
                            ${{ number_format($account['balance'], 2) }}
                        </div>
                    </div>
                    @endforeach
                    
                    <div class="line-item total">
                        <div class="account-info">Total Liabilities</div>
                        <div class="amount liabilities-total">
                            ${{ number_format($data['total_liabilities'], 2) }}
                        </div>
                    </div>
                </div>

                <!-- Equity Section -->
                <div class="section equity">
                    <h3>EQUITY</h3>
                    @foreach ($data['equity'] as $account)
                    <div class="line-item">
                        <div class="account-info">
                            <span class="account-code">{{ $account['code'] }}</span>
                            {{ $account['name'] }}
                        </div>
                        <div class="amount">
                            ${{ number_format($account['balance'], 2) }}
                        </div>
                    </div>
                    @endforeach
                    
                    @if ($data['retained_earnings'] != 0)
                    <div class="line-item">
                        <div class="account-info">
                            <span class="account-code">RE</span>
                            Retained Earnings (Current Period)
                        </div>
                        <div class="amount">
                            ${{ number_format($data['retained_earnings'], 2) }}
                        </div>
                    </div>
                    @endif
                    
                    <div class="line-item total">
                        <div class="account-info">Total Equity</div>
                        <div class="amount equity-total">
                            ${{ number_format($data['total_equity'], 2) }}
                        </div>
                    </div>
                </div>

                <!-- Total Liabilities + Equity -->
                <div class="line-item total" style="margin-top: 20px; border-top: 2px solid #ddd;">
                    <div class="account-info">Total Liabilities & Equity</div>
                    <div class="amount">
                        ${{ number_format($data['total_liabilities'] + $data['total_equity'], 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Assets</div>
                    <div class="summary-amount assets-total">
                        ${{ number_format($data['total_assets'], 2) }}
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Liabilities</div>
                    <div class="summary-amount liabilities-total">
                        ${{ number_format($data['total_liabilities'], 2) }}
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Equity</div>
                    <div class="summary-amount equity-total">
                        ${{ number_format($data['total_equity'], 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Balance Sheet as of {{ $asOfDate->format('F d, Y') }}</p>
            <p>Generated on {{ now()->format('F d, Y H:i:s') }} by {{ $brand['name'] ?? config('app.name') }}</p>
            @if (!$data['is_balanced'])
            <p style="color: #dc3545; font-weight: bold;">⚠️ WARNING: Balance Sheet is not balanced!</p>
            @endif
        </div>
    </div>
</body>
</html>