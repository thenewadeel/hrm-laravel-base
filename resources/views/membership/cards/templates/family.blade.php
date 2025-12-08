<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Member Card - {{ $member->full_name }}</title>
    <style>
        @page {
            size: 90mm 54mm;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: white;
        }
        
        .card {
            width: 90mm;
            height: 54mm;
            position: relative;
            background: linear-gradient(135deg, #ec4899 0%, #f472b6 50%, #f9a8d4 100%);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: rgba(255, 255, 255, 0.15);
            padding: 2mm 5mm;
            backdrop-filter: blur(10px);
        }
        
        .organization-name {
            color: white;
            font-size: 9pt;
            font-weight: bold;
            margin: 0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .family-badge {
            position: absolute;
            top: 2mm;
            right: 3mm;
            background: rgba(255, 255, 255, 0.9);
            color: #be185d;
            font-size: 6pt;
            font-weight: bold;
            padding: 1mm 2mm;
            border-radius: 3mm;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .card-body {
            display: flex;
            padding: 3mm 5mm;
            height: calc(54mm - 15mm);
        }
        
        .member-photo {
            width: 16mm;
            height: 20mm;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .member-info {
            flex: 1;
            margin-left: 3mm;
            color: white;
        }
        
        .member-name {
            font-size: 10pt;
            font-weight: bold;
            margin: 0 0 1mm 0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .member-details {
            font-size: 6pt;
            margin: 0;
            opacity: 0.9;
        }
        
        .relationship {
            font-size: 7pt;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5mm 2mm;
            border-radius: 2mm;
            display: inline-block;
            margin: 1mm 0;
        }
        
        .member-id {
            font-size: 7pt;
            font-weight: bold;
            margin: 1mm 0;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.5mm 2mm;
            border-radius: 2mm;
            display: inline-block;
        }
        
        .primary-member-info {
            font-size: 6pt;
            background: rgba(255, 255, 255, 0.1);
            padding: 1mm 2mm;
            border-radius: 2mm;
            margin-top: 1mm;
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
            width: 10mm;
            height: 10mm;
            background: white;
            border-radius: 2mm;
            padding: 1mm;
        }
        
        .barcode {
            width: 25mm;
            height: 6mm;
        }
        
        .status-indicator {
            position: absolute;
            top: 2mm;
            left: 3mm;
            width: 4mm;
            height: 4mm;
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
        
        .age-indicator {
            font-size: 6pt;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5mm 2mm;
            border-radius: 2mm;
            display: inline-block;
            margin-left: 2mm;
        }
        
        .family-icon {
            position: absolute;
            top: 50%;
            right: 5mm;
            transform: translateY(-50%);
            font-size: 16pt;
            color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="family-badge">Family</div>
        
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
                <div class="relationship">{{ $member->relationship ?? 'Member' }}</div>
                
                @if(isset($member->age))
                    <span class="age-indicator">Age: {{ $member->age }}</span>
                @endif
                
                <p class="member-details">{{ $member->email }}</p>
                <p class="member-details">{{ $member->phone }}</p>
                <div class="member-id">ID: {{ $member->membership_number ?? $member->barcode_number }}</div>
                
                @if(isset($primary_member))
                    <div class="primary-member-info">
                        Primary: {{ $primary_member->full_name }}
                    </div>
                @endif
            </div>
        </div>
        
        <div class="family-icon">👨‍👩‍👧‍👦</div>
        
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