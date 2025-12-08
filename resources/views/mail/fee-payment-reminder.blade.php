<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .fee-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .fee-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .fee-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.2em;
            color: #2563eb;
        }
        .overdue {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Reminder</h1>
        <p>{{ $member->full_name }}</p>
    </div>

    <div class="content">
        @if($isOverdue)
            <div class="overdue">
                <strong>⚠️ Important:</strong> Your payment is {{ $daysOverdue }} days overdue. Please make your payment as soon as possible to avoid additional late fees.
            </div>
        @endif

        <p>Dear {{ $member->first_name }},</p>

        <p>This is a friendly reminder that you have an outstanding fee that requires your attention.</p>

        @if($customMessage)
            <div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p><strong>Message:</strong> {{ $customMessage }}</p>
            </div>
        @endif

        <div class="fee-details">
            <h3>Fee Details</h3>
            <div class="fee-row">
                <span>Description:</span>
                <span>{{ $fee->description }}</span>
            </div>
            <div class="fee-row">
                <span>Type:</span>
                <span>{{ ucfirst(str_replace('_', ' ', $fee->fee_type)) }}</span>
            </div>
            <div class="fee-row">
                <span>Due Date:</span>
                <span>{{ $fee->due_date->format('F j, Y') }}</span>
            </div>
            <div class="fee-row">
                <span>Amount Due:</span>
                <span>${{ number_format($fee->amount, 2) }}</span>
            </div>
            <div class="fee-row">
                <span>Status:</span>
                <span style="color: {{ $isOverdue ? '#ef4444' : '#f59e0b' }}; font-weight: bold;">
                    {{ ucfirst($fee->status) }}{{ $isOverdue ? ' (' . $daysOverdue . ' days overdue)' : '' }}
                </span>
            </div>
        </div>

        <p><strong>Payment Methods Accepted:</strong></p>
        <ul>
            <li>Cash</li>
            <li>Bank Transfer</li>
            <li>Credit/Debit Card</li>
            <li>Online Payment</li>
            <li>Mobile Money</li>
        </ul>

        <p>If you have already made this payment, please disregard this notice. If you have any questions or need to make special arrangements, please contact us immediately.</p>

        <div style="text-align: center;">
            <a href="#" class="btn">Make Payment Now</a>
        </div>

        <p>Thank you for your prompt attention to this matter.</p>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>