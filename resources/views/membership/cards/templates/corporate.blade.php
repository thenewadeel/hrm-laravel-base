<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corporate Member Card - {{ $member->full_name }}</title>
    <style>
        @page {
            size: 90mm 54mm;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', Arial, sans-serif;
            background: white;
        }
        
        .card {
            width: 90mm;
            height: 54mm;
            position: relative;
            background: linear-gradient(180deg, #1e293b 0%, #334155 50%, #475569 100%);
            border: 1px solid #64748b;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .card-header {
            background: linear-gradient(90deg, #0f172a, #1e293b);
            padding: 3mm 5mm;
            border-bottom: 2px solid #3b82f6;
        }
        
        .organization-name {
            color: #f1f5f9;
            font-size: 10pt;
            font-weight: 600;
            margin: 0;
            letter-spacing: 0.5px;
        }
        
        .department-badge {
            position: absolute;
            top: 3mm;
            right: 3mm;
            background: #3b82f6;
            color: white;
            font-size: 6pt;
            font-weight: bold;
            padding: 1mm 2mm;
            border-radius: 1mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card-body {
            display: flex;
            padding: 4mm 5mm;
            height: calc(54mm - 18mm);
        }
        
        .member-photo {
            width: 18mm;
            height: 24mm;
            border-radius: 2px;
            object-fit: cover;
            border: 2px solid #3b82f6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .member-info {
            flex: 1;
            margin-left: 4mm;
            color: #f1f5f9;
        }
        
        .member-name {
            font-size: 11pt;
            font-weight: 600;
            margin: 0 0 2mm 0;
            color: #e2e8f0;
        }
        
        .member-details {
            font-size: 7pt;
            margin: 0;
            color: #94a3b8;
            line-height: 1.3;
        }
        
        .member-id {
            font-size: 8pt;
            font-weight: 600;
            margin: 2mm 0;
            background: rgba(59, 130, 246, 0.2);
            padding: 1mm 2mm;
            border-radius: 1mm;
            display: inline-block;
            border: 1px solid #3b82f6;
            color: #dbeafe;
        }
        
        .access-level {
            font-size: 7pt;
            background: rgba(34, 197, 94, 0.2);
            color: #bbf7d0;
            padding: 0.5mm 2mm;
            border-radius: 1mm;
            border: 1px solid #22c55e;
            display: inline-block;
            margin-top: 1mm;
        }
        
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            padding: 2mm 5mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #334155;
        }
        
        .qr-code {
            width: 10mm;
            height: 10mm;
            background: white;
            border-radius: 1mm;
            padding: 1mm;
            border: 1px solid #475569;
        }
        
        .barcode {
            width: 32mm;
            height: 8mm;
        }
        
        .status-indicator {
            position: absolute;
            top: 3mm;
            left: 3mm;
            width: 5mm;
            height: 5mm;
            border-radius: 50%;
            background: #22c55e;
            border: 1px solid #0f172a;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .status-indicator.expired {
            background: #ef4444;
        }
        
        .status-indicator.expiring-soon {
            background: #f59e0b;
        }
        
        .expiry-date {
            font-size: 6pt;
            color: #94a3b8;
            background: rgba(15, 23, 42, 0.8);
            padding: 1mm 2mm;
            border-radius: 1mm;
            border: 1px solid #334155;
        }
        
        .corporate-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 24pt;
            font-weight: bold;
            color: rgba(255, 255, 255, 0.05);
            text-transform: uppercase;
            letter-spacing: 2px;
            pointer-events: none;
        }
        
        .security-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 2px,
                rgba(59, 130, 246, 0.03) 2px,
                rgba(59, 130, 246, 0.03) 4px
            );
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="security-pattern"></div>
        <div class="corporate-watermark">Corporate</div>
        
        <div class="status-indicator {{ $member->isExpired() ? 'expired' : ($member->isExpiringSoon() ? 'expiring-soon' : '') }}"></div>
        
        <div class="card-header">
            <p class="organization-name">{{ $organization->name }}</p>
            <div class="department-badge">Corporate</div>
        </div>
        
        <div class="card-body">
            @if($design_settings['show_photo'] && $photo_url)
                <img src="{{ $photo_url }}" alt="{{ $member->full_name }}" class="member-photo">
            @endif
            
            <div class="member-info">
                <h3 class="member-name">{{ $member->full_name }}</h3>
                <p class="member-details">{{ $member->title }}</p>
                <p class="member-details">{{ $member->email }}</p>
                <p class="member-details">{{ $member->phone }}</p>
                <div class="member-id">ID: {{ $member->membership_number }}</div>
                <div class="access-level">Full Access</div>
                
                @if($design_settings['show_expiry'] && $expiry_date)
                    <div class="expiry-date">Expires: {{ $expiry_date }}</div>
                @endif
            </div>
        </div>
        
        <div class="card-footer">
            @if($design_settings['show_qr_code'])
                <img src="{{ $qr_code }}" alt="QR Code" class="qr-code">
            @endif
            
            @if($design_settings['show_barcode'])
                <img src="{{ $barcode }}" alt="Barcode" class="barcode">
            @endif
        </div>
    </div>
</body>
</html>