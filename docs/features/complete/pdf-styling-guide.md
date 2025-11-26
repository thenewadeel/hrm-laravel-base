# PDF Styling Customization Guide

## 🎨 Customizing PDF Output

The PDF generator supports multiple ways to customize the appearance and layout of your documentation.

### 📁 Configuration Files

#### 1. Simple Theme Configuration
Edit: `config/pdf-theme-simple.json`

```json
{
    "brand": {
        "name": "Your Project Name",
        "tagline": "Your Project Tagline",
        "logo": "🏢",
        "website": "https://your-project.com",
        "company": "Your Company",
        "version": "2.0.0"
    },
    "theme": {
        "primary_color": "#2563eb",
        "secondary_color": "#64748b",
        "background_color": "#ffffff",
        "text_color": "#1f2937",
        "border_color": "#e5e7eb",
        "header_font": "Arial, sans-serif",
        "body_font": "Arial, sans-serif",
        "code_font": "'Courier New', monospace"
    },
    "layout": {
        "page_size": "A4",
        "margin_top": "20mm",
        "margin_bottom": "20mm",
        "margin_left": "20mm",
        "margin_right": "20mm",
        "font_size": "12pt",
        "line_height": "1.6"
    }
}
```

#### 2. Advanced Theme Configuration
Edit: `config/pdf-theme.json` (for complex features)

```json
{
    "styling": {
        "header_style": "modern|classic|minimal",
        "footer_style": "modern|classic|minimal",
        "toc_style": "numbered|bulleted|minimal",
        "table_style": "striped|bordered|minimal",
        "link_style": "underline|colored|minimal",
        "blockquote_style": "bordered|shaded|minimal"
    },
    "features": {
        "auto_toc": true,
        "page_numbers": true,
        "section_breaks": true,
        "watermark": false,
        "bookmarks": true,
        "links": true
    },
    "custom": {
        "watermark_text": "DRAFT - CONFIDENTIAL",
        "confidential_text": "Company Confidential",
        "draft_mode": false,
        "print_date": true,
        "author_info": true
    }
}
```

### 🎨 CSS Customization Methods

#### Method 1: Override CSS Classes

Create a custom CSS file and reference it in your theme:

```css
/* Custom PDF Styles */
body {
    font-family: 'Georgia', serif;
    font-size: 11pt;
    line-height: 1.8;
    color: #2c3e50;
}

.document-header h1 {
    color: #1a5490;
    font-size: 28pt;
    font-weight: 300;
    text-align: center;
    border-bottom: 3px solid #1a5490;
    padding-bottom: 10px;
}

h1, h2, h3, h4, h5, h6 {
    color: #1a5490;
    font-weight: 700;
    border-left: 4px solid #1a5490;
    padding-left: 12px;
    background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
}

code {
    font-family: 'Fira Code', 'Consolas', monospace;
    background: #2d3748;
    color: #f8f9fa;
    border: 1px solid #44475a;
    border-radius: 6px;
    padding: 4px 8px;
}

pre {
    background: #2d3748;
    color: #f8f9fa;
    border: 1px solid #44475a;
    border-radius: 8px;
    padding: 16px;
    overflow-x: auto;
}

blockquote {
    border-left: 5px solid #1a5490;
    background: #f8f9fa;
    color: #6c757d;
    font-style: italic;
    padding: 16px 20px;
    margin: 20px 0;
    border-radius: 0 8px 8px 0;
}

table {
    border-collapse: collapse;
    width: 100%;
    margin: 20px 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    border: 1px solid #dee2e6;
    padding: 12px 16px;
    text-align: left;
}

th {
    background: #1a5490;
    color: white;
    font-weight: 600;
}

tr:nth-child(even) {
    background: #f8f9fa;
}

a {
    color: #1a5490;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

a:hover {
    border-bottom-color: #1a5490;
    color: #0d47a1;
}
```

#### Method 2: Use CSS Variables

```css
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --accent-color: #3b82f6;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
    --background-color: #ffffff;
    --text-color: #1f2937;
    --border-color: #e5e7eb;
    --header-font: 'Arial', sans-serif;
    --body-font: 'Georgia', serif;
    --code-font: 'Fira Code', monospace;
    --heading-font: 'Helvetica', sans-serif;
}

body {
    font-family: var(--body-font);
    color: var(--text-color);
    background-color: var(--background-color);
}

h1, h2, h3, h4, h5, h6 {
    font-family: var(--heading-font);
    color: var(--primary-color);
}

code, pre {
    font-family: var(--code-font);
    background: var(--background-color);
    border: 1px solid var(--border-color);
}
```

### 🎯 Theme Presets

#### Professional Corporate Theme
```json
{
    "brand": {
        "name": "Enterprise Documentation",
        "tagline": "Professional Business Solutions",
        "logo": "🏢",
        "company": "Your Company Name"
    },
    "theme": {
        "primary_color": "#1a365d",
        "secondary_color": "#64748b",
        "background_color": "#ffffff",
        "text_color": "#1f2937",
        "border_color": "#e5e7eb",
        "header_font": "Helvetica, Arial, sans-serif",
        "body_font": "Georgia, serif",
        "code_font": "'Courier New', monospace"
    }
}
```

#### Modern Tech Theme
```json
{
    "brand": {
        "name": "Tech Documentation",
        "tagline": "Modern Development Platform",
        "logo": "💻",
        "company": "Tech Corp"
    },
    "theme": {
        "primary_color": "#6366f1",
        "secondary_color": "#9ca3af",
        "background_color": "#f8fafc",
        "text_color": "#1e293b",
        "border_color": "#e2e8f0",
        "header_font": "'Inter', sans-serif",
        "body_font": "'Inter', sans-serif",
        "code_font": "'JetBrains Mono', monospace"
    }
}
```

#### Minimal Academic Theme
```json
{
    "brand": {
        "name": "Academic Documentation",
        "tagline": "Research & Education",
        "logo": "📚",
        "company": "University Name"
    },
    "theme": {
        "primary_color": "#2c3e50",
        "secondary_color": "#6b7280",
        "background_color": "#ffffff",
        "text_color": "#374151",
        "border_color": "#d1d5db",
        "header_font": "Times New Roman, serif",
        "body_font": "Times New Roman, serif",
        "code_font": "'Courier New', monospace"
    }
}
```

### 🔧 Advanced Customization

#### Custom Headers and Footers

```php
// In your custom PDF generator class
private function createCustomHeader($title, $chapter, $section): string
{
    return <<<HTML
<div class="custom-header">
    <div class="header-left">
        <div class="document-title">{$title}</div>
        <div class="chapter-info">Chapter {$chapter}</div>
    </div>
    <div class="header-right">
        <div class="section-info">{$section}</div>
        <div class="page-number">Page <span class="page-num"></span></div>
    </div>
</div>
HTML;
}

private function createCustomFooter($company, $copyright, $contact): string
{
    return <<<HTML
<div class="custom-footer">
    <div class="footer-left">
        <div class="company-name">{$company}</div>
        <div class="copyright">{$copyright}</div>
    </div>
    <div class="footer-right">
        <div class="contact-info">{$contact}</div>
        <div class="page-number">Page <span class="page-num"></span></div>
    </div>
</div>
HTML;
}
```

#### Watermarks and Backgrounds

```css
/* Watermark styles */
.watermark {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 120px;
    color: rgba(0, 0, 0, 0.1);
    font-weight: bold;
    z-index: -1;
    pointer-events: none;
}

.draft-watermark {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #ef4444;
    color: white;
    padding: 8px 16px;
    font-weight: bold;
    border-radius: 4px;
    font-size: 14px;
    z-index: 1000;
}

.confidential-watermark {
    position: fixed;
    top: 20px;
    left: 20px;
    background: #dc2626;
    color: white;
    padding: 8px 16px;
    font-weight: bold;
    border-radius: 4px;
    font-size: 14px;
    z-index: 1000;
}

/* Background patterns */
.background-pattern {
    background-image: 
        linear-gradient(45deg, #f8fafc 25%, transparent 25%),
        linear-gradient(-45deg, #f8fafc 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #f8fafc 75%),
        linear-gradient(-45deg, transparent 75%, #f8fafc 75%);
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px, 0 20px, 10px 30px;
}
```

### 📱 Responsive PDF Considerations

#### Mobile-Friendly Layouts
```css
/* Mobile-specific PDF styles */
@media screen and (max-width: 768px) {
    .document {
        margin: 10px;
        padding: 15px;
    }
    
    .document-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    h1 {
        font-size: 20pt;
    }
    
    h2 {
        font-size: 16pt;
    }
    
    .document-meta {
        font-size: 8pt;
    }
    
    table {
        font-size: 9pt;
        margin: 10px 0;
    }
    
    code, pre {
        font-size: 8pt;
        padding: 8px;
    }
}
```

### 🎨 Typography Best Practices

#### Font Pairing
```css
/* Professional font combinations */
.font-combination-1 {
    --header-font: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    --body-font: 'Georgia', Times, serif;
    --code-font: 'Consolas', 'Courier New', monospace;
}

.font-combination-2 {
    --header-font: 'Inter', sans-serif;
    --body-font: 'Inter', sans-serif;
    --code-font: 'JetBrains Mono', 'Fira Code', monospace;
}

.font-combination-3 {
    --header-font: 'Times New Roman', Times, serif;
    --body-font: 'Times New Roman', Times, serif;
    --code-font: 'Courier New', monospace;
}
```

#### Hierarchy and Spacing
```css
/* Clear visual hierarchy */
h1 { font-size: 28pt; font-weight: 300; margin: 0 0 24pt 0; }
h2 { font-size: 22pt; font-weight: 400; margin: 24pt 0 16pt 0; }
h3 { font-size: 18pt; font-weight: 500; margin: 20pt 0 12pt 0; }
h4 { font-size: 16pt; font-weight: 600; margin: 18pt 0 10pt 0; }
h5 { font-size: 14pt; font-weight: 600; margin: 16pt 0 8pt 0; }
h6 { font-size: 12pt; font-weight: 600; margin: 14pt 0 6pt 0; }

p { margin: 12pt 0; line-height: 1.6; orphans: 3; widows: 3; }
```

### 🔧 Testing Your Styles

#### Preview Before Generation
```bash
# Generate HTML preview first
composer run docs-pdf-simple --force-html

# Open in browser to test
open docs/pdf/index.html
# or
xdg-open docs/pdf/index.html
```

#### Print Testing
```bash
# Generate test PDF
composer run docs-pdf-simple

# Check PDF properties
pdfinfo docs/pdf/your-file.pdf

# Test print layout
evince docs/pdf/your-file.pdf
# or
okular docs/pdf/your-file.pdf
```

### 📋 Quick Reference

#### Common CSS Properties
```css
/* Page Layout */
@page {
    size: A4; /* A4, Letter, Legal */
    margin: 20mm; /* Top, Right, Bottom, Left */
    orientation: portrait; /* portrait, landscape */
}

/* Typography */
font-family: 'Font Name', fallback;
font-size: 12pt;
font-weight: 400; /* 100-900 */
line-height: 1.6;
letter-spacing: 0.5pt;

/* Colors */
color: #333333; /* Text */
background-color: #ffffff; /* Background */
border-color: #e5e7eb; /* Borders */

/* Spacing */
margin: 20pt 0; /* Top/Bottom, Left/Right */
padding: 12pt 16pt; /* Top/Right/Bottom/Left */
```

### 🚀 Implementation Tips

1. **Start Simple**: Begin with basic colors and fonts
2. **Test Early**: Generate test PDFs frequently
3. **Iterate**: Make small adjustments and test
4. **Document**: Keep notes on what works best
5. **Version Control**: Track changes to your theme files

### 📞 Support

For styling issues or questions:
1. Check generated HTML first: `docs/pdf/*.html`
2. Test with different PDF engines
3. Use browser developer tools for CSS debugging
4. Reference this guide for common patterns

---

*This guide provides comprehensive customization options for professional PDF documentation styling.*