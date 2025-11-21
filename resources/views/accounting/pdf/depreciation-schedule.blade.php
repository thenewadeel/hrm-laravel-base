<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Depreciation Schedule</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .asset-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .asset-header {
            background-color: #f2f2f2;
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 0;
        }
        .asset-info {
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f9f9f9;
            font-weight: bold;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .method-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .straight-line {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .declining-balance {
            background-color: #fff3e0;
            color: #f57c00;
        }
        .sum-of-years {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Depreciation Schedule</h1>
        <p>As of {{ now()->format('F j, Y') }}</p>
        <p>{{ auth()->user()->currentOrganization->name }}</p>
    </div>

    @foreach($scheduleData as $data)
        <div class="asset-section">
            <div class="asset-header">
                <div class="asset-info">
                    <strong>{{ $data['asset']->asset_tag }} - {{ $data['asset']->name }}</strong>
                    <span>Category: {{ $data['asset']->category?->name }}</span>
                    <span class="method-badge 
                        @if($data['asset']->depreciation_method === 'straight_line') straight-line
                        @elseif($data['asset']->depreciation_method === 'declining_balance') declining-balance
                        @else sum-of-years
                        @endif">
                        {{ $data['asset']->depreciation_method === 'straight_line' ? 'Straight Line' : 
                           ($data['asset']->depreciation_method === 'declining_balance' ? 'Declining Balance' : 'Sum of Years') }}
                    </span>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Purchase Cost</td>
                        <td class="text-right">{{ number_format($data['asset']->purchase_cost, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Salvage Value</td>
                        <td class="text-right">{{ number_format($data['asset']->salvage_value, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Useful Life (Years)</td>
                        <td class="text-right">{{ $data['asset']->useful_life_years }}</td>
                    </tr>
                    <tr>
                        <td>Current Book Value</td>
                        <td class="text-right">{{ number_format($data['asset']->current_book_value, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Accumulated Depreciation</td>
                        <td class="text-right">{{ number_format($data['asset']->accumulated_depreciation, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Annual Depreciation</td>
                        <td class="text-right">{{ number_format($data['annual_depreciation'], 2) }}</td>
                    </tr>
                    <tr>
                        <td>Remaining Useful Life</td>
                        <td class="text-right">{{ $data['remaining_life'] }} years</td>
                    </tr>
                </tbody>
            </table>

            @if(count($data['projected_depreciation']) > 0)
                <h4>Projected Depreciation Schedule</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th class="text-right">Depreciation</th>
                            <th class="text-right">Book Value End of Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['projected_depreciation'] as $projection)
                            <tr>
                                <td>{{ $projection['year'] }}</td>
                                <td class="text-right">{{ number_format($projection['depreciation'], 2) }}</td>
                                <td class="text-right">{{ number_format($projection['book_value'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

    <div class="footer">
        <p>Generated on {{ now()->format('F j, Y g:i A') }}</p>
    </div>
</body>
</html>