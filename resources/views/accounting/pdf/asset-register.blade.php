<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fixed Asset Register</title>
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
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
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
        <h1>Fixed Asset Register</h1>
        <p>As of {{ now()->format('F j, Y') }}</p>
        <p>{{ auth()->user()->currentOrganization->name }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Asset Tag</th>
                <th>Name</th>
                <th>Category</th>
                <th>Purchase Date</th>
                <th>Purchase Cost</th>
                <th>Accum. Depreciation</th>
                <th>Book Value</th>
                <th>Status</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
                <tr>
                    <td>{{ $asset->asset_tag }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->category?->name }}</td>
                    <td>{{ $asset->purchase_date->format('M d, Y') }}</td>
                    <td class="text-right">{{ number_format($asset->purchase_cost, 2) }}</td>
                    <td class="text-right">{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                    <td class="text-right">{{ number_format($asset->current_book_value, 2) }}</td>
                    <td class="text-center">{{ ucfirst($asset->status) }}</td>
                    <td>{{ $asset->location }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-row">
            <strong>Total Assets:</strong>
            <span>{{ $assets->count() }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Purchase Cost:</strong>
            <span>{{ number_format($totalCost, 2) }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Accumulated Depreciation:</strong>
            <span>{{ number_format($totalAccumulatedDepreciation, 2) }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Book Value:</strong>
            <span>{{ number_format($totalBookValue, 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Generated on {{ now()->format('F j, Y g:i A') }}</p>
    </div>
</body>
</html>