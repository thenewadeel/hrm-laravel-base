<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Card - {{ $member->full_name }}</title>
    <style>
        @page {
            size: 90mm 54mm;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: {{ $design_settings['font_family'] ?? 'Arial' }}, sans-serif;
            background: white;
        }
        
        .card {
            width: 90mm;
            height: 54mm;
            position: relative;
            background: linear-gradient(135deg, {{ $design_settings['primary_color'] ?? '#1e40af' }} 0%, {{ $design_settings['secondary_color'] ?? '#3b82f6' }} 100%);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 3mm 5mm;
            backdrop-filter: blur(10px);
        }
        
        .organization-name {
            color: white;
            font-size: 10pt;
            font-weight: bold;
            margin: 0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .card-body {
            display: flex;
            padding: 4mm 5mm;
            height: calc(54mm - 15mm);
        }
        
        .member-photo {
            width: 20mm;
            height: 25mm;
            border-radius: 4px;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .member-info {
            flex: 1;
            margin-left: 4mm;
            color: white;
        }
        
        .member-name {
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 2mm 0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .member-details {
            font-size: 7pt;
            margin: 0;
            opacity: 0.9;
        }
        
        .member-id {
            font-size: 8pt;
            font-weight: bold;
            margin: 2mm 0;
            background: rgba(255, 255, 255, 0.2);
            padding: 1mm 2mm;
            border-radius: 2mm;
            display: inline-block;
        }
        
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.2);
            padding: 2mm 5mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .qr-code {
            width: 12mm;
            height: 12mm;
            background: white;
            border-radius: 2mm;
            padding: 1mm;
        }
        
        .barcode {
            width: 30mm;
            height: 8mm;
        }
        
        .status-indicator {
            position: absolute;
            top: 3mm;
            right: 3mm;
            width: 6mm;
            height: 6mm;
            border-radius: 50%;
            background: #10b981;
            border: 1px solid white;
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
            color: white;
            background: rgba(0, 0, 0, 0.3);
            padding: 1mm 2mm;
            border-radius: 1mm;
        }
        
        .premium-badge {
            position: absolute;
            top: 3mm;
            left: 3mm;
            background: linear-gradient(45deg, #fbbf24, #f59e0b);
            color: #7c2d12;
            font-size: 6pt;
            font-weight: bold;
            padding: 1mm 2mm;
            border-radius: 2mm;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="card">
        @if($design_settings['card_type'] === 'premium')
            <div class="premium-badge">Premium</div>
        @endif
        
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
                <div class="member-id">ID: {{ $member->membership_number }}</div>
                
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