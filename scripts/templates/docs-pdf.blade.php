<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{title}</title>
    <style>
{css}
    </style>
    <style>
        /* Aggressive margin reset for PDF generation */
        @page {
            margin: {page_margin} !important;
            size: {page_size} !important;
            padding: 0 !important;
        }
        
        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        
        .document {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            position: absolute !important;
            top: {margin_top} !important;
            left: {margin_left} !important;
            right: {margin_right} !important;
            bottom: {margin_bottom} !important;
        }
        
        * {
            box-sizing: border-box !important;
        }
    </style>
</head>
<body>
    {watermark_html}
    <div class="document">
        {header_html}
        
        {nav_html}
        
        <main class="content">
            {content}
        </main>
        
        {footer_html}
    </div>
</body>
</html>