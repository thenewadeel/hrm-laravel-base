<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payslip->employee->user->name }} - {{ \Carbon\Carbon::parse($payslip->period)->format('F Y') }}</title>
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
        
        .payslip-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            background: #fff;
        }
        
        .header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #007bff;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .company-info h2 {
            margin: 0 0 5px 0;
            color: #007bff;
            font-size: 20px;
        }
        
        .company-info p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }
        
        .period-info {
            text-align: right;
        }
        
        .period-info h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }
        
        .period-info p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }
        
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status.paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status.processed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .employee-details {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .employee-details div {
            margin-bottom: 5px;
        }
        
        .employee-details strong {
            display: block;
            font-size: 11px;
            color: #666;
            margin-bottom: 2px;
        }
        
        .content {
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section h4 {
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #ddd;
            font-size: 14px;
        }
        
        .earnings h4 {
            color: #28a745;
            border-color: #28a745;
        }
        
        .deductions h4 {
            color: #dc3545;
            border-color: #dc3545;
        }
        
        .line-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .line-item dt {
            font-weight: normal;
        }
        
        .line-item dd {
            font-weight: bold;
            margin-left: 20px;
        }
        
        .total {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #ddd;
            font-weight: bold;
        }
        
        .gross-total {
            color: #28a745;
            font-size: 14px;
        }
        
        .deductions-total {
            color: #dc3545;
            font-size: 14px;
        }
        
        .net-pay {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            margin: 20px 0 0 0;
        }
        
        .net-pay h4 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        
        .net-pay .amount {
            font-size: 24px;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            padding: 15px 20px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        
        @media print {
            body { margin: 0; padding: 10px; }
            .payslip-container { box-shadow: none; }
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h2>{{ config('app.name') }} Payroll</h2>
                <p>{{ config('app.address', '123 Business Blvd, Suite 400') }}</p>
                <p>{{ config('app.city', 'City, State 90210') }}</p>
            </div>
            <div class="period-info">
                <h3>{{ \Carbon\Carbon::parse($payslip->period)->format('F Y') }}</h3>
                <p>Pay Period: {{ \Carbon\Carbon::parse($payslip->period)->format('F d, Y') }}</p>
                <p>Paid Date: {{ $payslip->paid_at ? $payslip->paid_at->format('M d, Y') : 'N/A' }}</p>
                <span class="status {{ $payslip->status }}">{{ ucfirst($payslip->status) }}</span>
            </div>
        </div>

        <!-- Employee Details -->
        <div class="employee-details">
            <div>
                <strong>Employee Name:</strong>
                {{ $payslip->employee->user->name }}
            </div>
            <div>
                <strong>Employee ID:</strong>
                EMP-{{ str_pad($payslip->employee_id, 4, '0', STR_PAD_LEFT) }}
            </div>
            <div>
                <strong>Period:</strong>
                {{ \Carbon\Carbon::parse($payslip->period)->format('Y-m') }}
            </div>
            <div>
                <strong>Status:</strong>
                {{ ucfirst($payslip->status) }}
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Earnings -->
            <div class="section earnings">
                <h4>EARNINGS</h4>
                <div class="line-item">
                    <dt>Basic Salary</dt>
                    <dd>${{ number_format($payslip->basic_salary, 2) }}</dd>
                </div>
                <div class="line-item">
                    <dt>Housing Allowance</dt>
                    <dd>${{ number_format($payslip->housing_allowance, 2) }}</dd>
                </div>
                <div class="line-item">
                    <dt>Transport Allowance</dt>
                    <dd>${{ number_format($payslip->transport_allowance, 2) }}</dd>
                </div>
                <div class="line-item">
                    <dt>Overtime Pay</dt>
                    <dd>${{ number_format($payslip->overtime_pay, 2) }}</dd>
                </div>
                <div class="line-item">
                    <dt>Bonus</dt>
                    <dd>${{ number_format($payslip->bonus, 2) }}</dd>
                </div>
                <div class="line-item total gross-total">
                    <dt>GROSS PAY</dt>
                    <dd>${{ number_format($payslip->gross_pay, 2) }}</dd>
                </div>
            </div>

            <!-- Deductions -->
            <div class="section deductions">
                <h4>DEDUCTIONS</h4>
                <div class="line-item">
                    <dt>Income Tax (PAYE)</dt>
                    <dd>(${{ number_format($payslip->tax_deduction, 2) }})</dd>
                </div>
                <div class="line-item">
                    <dt>Health Insurance</dt>
                    <dd>(${{ number_format($payslip->insurance_deduction, 2) }})</dd>
                </div>
                <div class="line-item">
                    <dt>Social Security / Pension</dt>
                    <dd>(${{ number_format($payslip->social_security_deduction ?? 0, 2) }})</dd>
                </div>
                <div class="line-item">
                    <dt>Other Deductions</dt>
                    <dd>(${{ number_format($payslip->other_deductions, 2) }})</dd>
                </div>
                <div class="line-item total deductions-total">
                    <dt>TOTAL DEDUCTIONS</dt>
                    <dd>(${{ number_format($payslip->total_deductions, 2) }})</dd>
                </div>
            </div>
        </div>

        <!-- Net Pay -->
        <div class="net-pay">
            <h4>NET PAY</h4>
            <div class="amount">${{ number_format($payslip->net_pay, 2) }}</div>
            <p>This is the amount deposited into your bank account.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Payslip generated on {{ now()->format('M d, Y H:i A') }}</p>
            <p>This is a computer-generated document and requires no signature.</p>
        </div>
    </div>
</body>
</html>