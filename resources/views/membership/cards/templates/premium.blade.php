<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Member Card - {{ $member->full_name }}</title>
    <style>
        @page {
            size: 90mm 54mm;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Georgia', serif;
            background: white;
        }
        
        .card {
            width: 90mm;
            height: 54mm;
            position: relative;
            background: linear-gradient(135deg, #b8860b 0%, #ffd700 50%, #b8860b 100%);
            border: 2px solid #8b6914;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 2px,
                rgba(255, 255, 255, 0.1) 2px,
                rgba(255, 255, 255, 0.1) 4px
            );
            animation: shimmer 20s linear infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .card-header {
            background: linear-gradient(90deg, rgba(139, 69, 19, 0.9), rgba(184, 134, 11, 0.9));
            padding: 3mm 5mm;
            position: relative;
            z-index: 2;
        }
        
        .organization-name {
            color: #ffd700;
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            font-family: 'Times New Roman', serif;
        }
        
        .premium-ribbon {
            position: absolute;
            top: 8mm;
            right: -8mm;
            background: #dc2626;
            color: white;
            padding: 2mm 8mm;
            font-size: 7pt;
            font-weight: bold;
            transform: rotate(45deg);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            z-index: 3;
        }
        
        .card-body {
            display: flex;
            padding: 4mm 5mm;
            height: calc(54mm - 20mm);
            position: relative;
            z-index: 2;
        }
        
        .member-photo {
            width: 22mm;
            height: 28mm;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ffd700;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4), inset 0 0 10px rgba(255, 215, 0, 0.5);
        }
        
        .member-info {
            flex: 1;
            margin-left: 4mm;
            color: #2c1810;
        }
        
        .member-name {
            font-size: 13pt;
            font-weight: bold;
            margin: 0 0 2mm 0;
            text-shadow: 0 1px 2px rgba(255, 215, 0, 0.3);
            font-family: 'Times New Roman', serif;
        }
        
        .member-details {
            font-size: 8pt;
            margin: 0;
            font-weight: 500;
        }
        
        .member-id {
            font-size: 9pt;
            font-weight: bold;
            margin: 2mm 0;
            background: rgba(255, 215, 0, 0.3);
            padding: 1mm 3mm;
            border-radius: 3mm;
            display: inline-block;
            border: 1px solid #b8860b;
        }
        
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(139, 69, 19, 0.8);
            padding: 2mm 5mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 2;
        }
        
        .qr-code {
            width: 12mm;
            height: 12mm;
            background: white;
            border-radius: 2mm;
            padding: 1mm;
            border: 1px solid #ffd700;
        }
        
        .barcode {
            width: 35mm;
            height: 10mm;
        }
        
        .status-indicator {
            position: absolute;
            top: 3mm;
            right: 3mm;
            width: 7mm;
            height: 7mm;
            border-radius: 50%;
            background: radial-gradient(circle, #10b981, #059669);
            border: 2px solid #ffd700;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            z-index: 3;
        }
        
        .status-indicator.expired {
            background: radial-gradient(circle, #ef4444, #dc2626);
        }
        
        .status-indicator.expiring-soon {
            background: radial-gradient(circle, #f59e0b, #d97706);
        }
        
        .expiry-date {
            font-size: 7pt;
            color: #2c1810;
            background: rgba(255, 215, 0, 0.4);
            padding: 1mm 2mm;
            border-radius: 2mm;
            border: 1px solid #b8860b;
            font-weight: bold;
        }
        
        .premium-seal {
            position: absolute;
            bottom: 15mm;
            right: 5mm;
            width: 15mm;
            height: 15mm;
            background: radial-gradient(circle, #ffd700, #b8860b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            font-weight: bold;
            color: #2c1810;
            border: 2px solid #8b6914;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
            z-index: 3;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="premium-ribbon">PREMIUM</div>
        
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
        
        <div class="premium-seal">VIP</div>
        
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