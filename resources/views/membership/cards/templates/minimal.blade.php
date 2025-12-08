<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimal Member Card - {{ $member->full_name }}</title>
    <style>
        @page {
            size: 90mm 54mm;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: white;
        }
        
        .card {
            width: 90mm;
            height: 54mm;
            position: relative;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: #f9fafb;
            padding: 4mm 5mm;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .organization-name {
            color: #374151;
            font-size: 8pt;
            font-weight: 500;
            margin: 0;
            letter-spacing: 0.5px;
        }
        
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 6mm 5mm;
            height: calc(54mm - 20mm);
            text-align: center;
        }
        
        .member-name {
            font-size: 12pt;
            font-weight: 600;
            margin: 0 0 2mm 0;
            color: #111827;
        }
        
        .member-id {
            font-size: 8pt;
            color: #6b7280;
            background: #f3f4f6;
            padding: 1mm 3mm;
            border-radius: 2mm;
            display: inline-block;
            font-family: 'SF Mono', Monaco, monospace;
        }
        
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f9fafb;
            padding: 3mm 5mm;
            display: flex;
            justify-content: center;
            align-items: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .qr-code {
            width: 12mm;
            height: 12mm;
            background: white;
            border-radius: 1mm;
            padding: 1mm;
            border: 1px solid #e5e7eb;
        }
        
        .status-indicator {
            position: absolute;
            top: 3mm;
            right: 3mm;
            width: 3mm;
            height: 3mm;
            border-radius: 50%;
            background: #10b981;
        }
        
        .status-indicator.expired {
            background: #ef4444;
        }
        
        .status-indicator.expiring-soon {
            background: #f59e0b;
        }
        
        .member-type {
            font-size: 6pt;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1mm;
        }
        
        .minimal-divider {
            width: 20mm;
            height: 1px;
            background: #e5e7eb;
            margin: 3mm auto;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="status-indicator {{ $member->isExpired() ? 'expired' : ($member->isExpiringSoon() ? 'expiring-soon' : '') }}"></div>
        
        <div class="card-header">
            <p class="organization-name">{{ $organization->name }}</p>
        </div>
        
        <div class="card-body">
            <h3 class="member-name">{{ $member->full_name }}</h3>
            <div class="member-id">{{ $member->membership_number ?? $member->barcode_number }}</div>
            <div class="minimal-divider"></div>
            <div class="member-type">{{ $design_settings['card_type'] ?? 'Member' }}</div>
        </div>
        
        <div class="card-footer">
            @if($design_settings['show_qr_code'])
                <img src="{{ $qr_code }}" alt="QR Code" class="qr-code">
            @endif
        </div>
    </div>
</body>
</html>