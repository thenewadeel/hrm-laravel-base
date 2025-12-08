<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classic Member Card - {{ $member->full_name }}</title>
    <style>
        @page {
            size: 90mm 54mm;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', serif;
            background: white;
        }
        
        .card {
            width: 90mm;
            height: 54mm;
            position: relative;
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            border: 2px solid #475569;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: #1e293b;
            padding: 3mm 5mm;
            text-align: center;
        }
        
        .organization-name {
            color: white;
            font-size: 10pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .card-body {
            display: flex;
            padding: 4mm 5mm;
            height: calc(54mm - 16mm);
        }
        
        .member-photo {
            width: 20mm;
            height: 25mm;
            object-fit: cover;
            border: 2px solid #1e293b;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .member-info {
            flex: 1;
            margin-left: 4mm;
            color: #1e293b;
        }
        
        .member-name {
            font-size: 11pt;
            font-weight: bold;
            margin: 0 0 2mm 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .member-details {
            font-size: 7pt;
            margin: 0;
            line-height: 1.4;
        }
        
        .member-id {
            font-size: 8pt;
            font-weight: bold;
            margin: 2mm 0;
            background: #f1f5f9;
            padding: 1mm 2mm;
            border: 1px solid #cbd5e1;
            display: inline-block;
            font-family: 'Courier New', monospace;
        }
        
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            padding: 2mm 5mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .qr-code {
            width: 10mm;
            height: 10mm;
            background: white;
            padding: 1mm;
            border: 1px solid #475569;
        }
        
        .barcode {
            width: 30mm;
            height: 8mm;
        }
        
        .status-indicator {
            position: absolute;
            top: 3mm;
            right: 3mm;
            width: 5mm;
            height: 5mm;
            border-radius: 50%;
            background: #10b981;
            border: 1px solid #1e293b;
        }
        
        .status-indicator.expired {
            background: #ef4444;
        }
        
        .status-indicator.expiring-soon {
            background: #f59e0b;
        }
        
        .expiry-date {
            font-size: 6pt;
            color: #64748b;
            background: #f8fafc;
            padding: 1mm 2mm;
            border-radius: 1mm;
            border: 1px solid #cbd5e1;
            display: inline-block;
            margin-top: 1mm;
        }
        
        .classic-border {
            position: absolute;
            top: 2mm;
            left: 2mm;
            right: 2mm;
            bottom: 2mm;
            border: 1px solid #cbd5e1;
            border-radius: 2px;
            pointer-events: none;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 20pt;
            font-weight: bold;
            color: rgba(30, 41, 59, 0.05);
            text-transform: uppercase;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="classic-border"></div>
        <div class="watermark">Member</div>
        
        <div class="status-indicator {{ $member->isExpired() ? 'expired' : ($member->isExpiringSoon() ? 'expiring-soon' : '') }}"></div>
        
        <div class="card-header">
            <p class="organization-name">{{ $organization->name }}</p>
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
                <div class="member-id">{{ $member->membership_number }}</div>
                
                @if($design_settings['show_expiry'] && $expiry_date)
                    <div class="expiry-date">Valid Until: {{ $expiry_date }}</div>
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