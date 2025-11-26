<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $data['title'] }}</title>
    <style>
        @include('accounting.pdf.styles')
        
        .aging-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .aging-item {
            flex: 1;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
        }
        
        .aging-current { background-color: #d4edda; }
        .aging-30 { background-color: #fff3cd; }
        .aging-60 { background-color: #ffeaa7; }
        .aging-90 { background-color: #f8d7da; }
        
        .customer-details {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .invoice-table {
            margin-top: 10px;
            font-size: 0.9em;
        }
        
        .invoice-table th {
            background-color: #e9ecef;
            padding: 8px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        
        .invoice-table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @include('accounting.pdf.header', ['title' => $data['title']])

    <!-- Report Information -->
    <div class="report-info">
        <p><strong>As Of Date:</strong> {{ $data['as_of_date'] }}</p>
        <p><strong>Period:</strong> {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</p>
        <p><strong>Generated On:</strong> {{ $data['generated_at'] }}</p>
    </div>

    <!-- Aging Summary -->
    <h3>Summary</h3>
    <div class="aging-summary">
        <div class="aging-item aging-current">
            <strong>Total Outstanding</strong><br>
            <span style="font-size: 1.5em;">{{ number_format($data['summary']['total_outstanding'], 2) }}</span>
        </div>
        <div class="aging-item aging-current">
            <strong>Current</strong><br>
            <span style="font-size: 1.2em;">{{ number_format($data['summary']['aging']['current'], 2) }}</span>
        </div>
        <div class="aging-item aging-30">
            <strong>30 Days</strong><br>
            <span style="font-size: 1.2em;">{{ number_format($data['summary']['aging']['30_days'], 2) }}</span>
        </div>
        <div class="aging-item aging-60">
            <strong>60 Days</strong><br>
            <span style="font-size: 1.2em;">{{ number_format($data['summary']['aging']['60_days'], 2) }}</span>
        </div>
        <div class="aging-item aging-90">
            <strong>90+ Days</strong><br>
            <span style="font-size: 1.2em;">{{ number_format($data['summary']['aging']['90_days'], 2) }}</span>
        </div>
    </div>

    <!-- Customer Statements -->
    <h3>Customer Outstanding Details</h3>
    @foreach($data['customers'] as $index => $customer)
        @if($index > 0)
            <div class="page-break"></div>
        @endif
        
        <div class="customer-details">
            <h4>{{ $customer['customer_name'] }}</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%;"><strong>Email:</strong> {{ $customer['email'] ?? 'N/A' }}</td>
                    <td style="width: 50%;"><strong>Phone:</strong> {{ $customer['phone'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Total Outstanding:</strong> {{ number_format($customer['total_outstanding'], 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Aging Breakdown -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background-color: #e9ecef;">
                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Current</th>
                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">30 Days</th>
                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">60 Days</th>
                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">90+ Days</th>
                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 10px; border: 1px solid #dee2e6;">{{ number_format($customer['current'], 2) }}</td>
                    <td style="padding: 10px; border: 1px solid #dee2e6;">{{ number_format($customer['30_days'], 2) }}</td>
                    <td style="padding: 10px; border: 1px solid #dee2e6;">{{ number_format($customer['60_days'], 2) }}</td>
                    <td style="padding: 10px; border: 1px solid #dee2e6;">{{ number_format($customer['90_days'], 2) }}</td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; font-weight: bold;">{{ number_format($customer['total_outstanding'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Invoice Details -->
        <h5>Invoice Details</h5>
        <table class="invoice-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Days Overdue</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customer['entries'] as $entry)
                    <tr>
                        <td>{{ $entry['invoice_number'] ?? $entry['reference_number'] }}</td>
                        <td>{{ $entry['entry_date'] }}</td>
                        <td>{{ $entry['due_date'] }}</td>
                        <td>{{ $entry['days_overdue'] }}</td>
                        <td style="text-align: right;">{{ number_format($entry['total_amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    @include('accounting.pdf.footer')
</body>
</html>