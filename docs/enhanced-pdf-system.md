# Enhanced PDF Documentation System

## 🎨 **Professional PDF Generation with Branding**

The PDF documentation system has been completely overhauled to provide professional, branded PDFs with comprehensive customization options.

---

## ✨ **New Features**

### **🏢 Professional Branding**
- **Company Information**: Name, logo, website, copyright
- **Version Control**: Automatic versioning and date stamping
- **Visual Identity**: Custom colors and typography
- **Professional Headers**: Dynamic headers with document info
- **Styled Footers**: Page numbers and company details

### **🎨 Advanced Theming**
- **Color Schemes**: Primary, secondary, accent colors
- **Typography**: Custom fonts for headers, body, code
- **Layout Control**: Margins, page sizes, spacing
- **Style Variants**: Modern, classic, minimal themes
- **Responsive Design**: Optimized for both screen and print

### **📋 Rich Content Features**
- **Auto Table of Contents**: Generated from document headings
- **Page Breaks**: Smart section separation
- **Cross-References**: Clickable links and bookmarks
- **Code Highlighting**: GitHub-style syntax highlighting
- **Watermark Support**: Optional text watermarks
- **Draft Mode**: Visual draft indicators

### **⚙️ Configuration System**
- **JSON Configuration**: `config/pdf-theme.json`
- **CLI Configurator**: Interactive theme setup
- **Composer Integration**: Easy command access
- **Hot Reloading**: Live configuration updates
- **Validation**: Input validation and error handling

---

## 🚀 **Usage**

### **Basic PDF Generation**
```bash
# Generate PDFs with current theme
composer run docs-pdf

# Generate with verbose output
composer run docs-pdf --verbose
```

### **Theme Configuration**
```bash
# Interactive theme configurator
composer run docs-theme-interactive

# Quick brand name change
composer run docs-theme "My Company"

# View current configuration
php scripts/configure-pdf-theme.php
# Choose option 6 (Show Configuration)
```

### **Custom Generation Options**
```bash
# Force HTML generation (no PDF tools)
php scripts/generate-docs-pdf.php --force-html

# Custom time window (last 6 hours)
php scripts/generate-docs-pdf.php --age 6

# Clean up intermediate files
php scripts/generate-docs-pdf.php --cleanup-html

# Full verbose output
php scripts/generate-docs-pdf.php --verbose
```

---

## 📁 **Configuration Structure**

### **Theme Configuration File** (`config/pdf-theme.json`)

```json
{
    "brand": {
        "name": "HRM Laravel Base",
        "tagline": "Enterprise ERP System", 
        "logo": "🏢",
        "website": "https://hrm-laravel-base.example.com",
        "company": "HRM Solutions",
        "version": "2.0.0",
        "copyright": "© 2025 HRM Solutions. All rights reserved."
    },
    "theme": {
        "primary_color": "#2563eb",
        "secondary_color": "#64748b",
        "accent_color": "#3b82f6",
        "success_color": "#10b981",
        "warning_color": "#f59e0b",
        "error_color": "#ef4444",
        "background_color": "#ffffff",
        "text_color": "#1f2937",
        "border_color": "#e5e7eb",
        "header_font": "Arial, sans-serif",
        "body_font": "Georgia, serif",
        "code_font": "'Courier New', monospace"
    },
    "layout": {
        "page_size": "A4",
        "margin_top": "20mm",
        "margin_bottom": "20mm",
        "margin_left": "20mm", 
        "margin_right": "20mm",
        "font_size": "11pt",
        "line_height": "1.6",
        "header_height": "15mm",
        "footer_height": "15mm"
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
        "watermark_text": "",
        "confidential_text": "",
        "draft_mode": false,
        "print_date": true,
        "author_info": true
    }
}
```

---

## 🎯 **Output Examples**

### **Professional PDF Features**
- **Dynamic Headers**: Document title, version, date, page numbers
- **Styled Footers**: Company info, website, copyright
- **Table of Contents**: Auto-generated with page numbers
- **Consistent Branding**: Logo and colors throughout
- **Professional Typography**: Optimized fonts for readability
- **Smart Layouts**: Proper margins and spacing
- **Visual Hierarchy**: Clear heading structure
- **Code Blocks**: Syntax highlighted with proper styling
- **Tables**: Professional bordered tables
- **Links**: Clickable cross-references

### **Sample PDF Layout**
```
┌─────────────────────────────────────────────┐
│ 🏢 HRM Laravel Base                    Page 1 │
│ Enterprise ERP System    v2.0.0  2025-11-20    │
├─────────────────────────────────────────────┤
│                                             │
│  📋 Table of Contents                        │
│                                             │
│  1. Introduction ................................ 1 │
│  2. Architecture ................................ 3 │
│  3. Features .................................... 5 │
│                                             │
├─────────────────────────────────────────────┤
│                                             │
│  # Introduction                             │
│                                             │
│  Welcome to the HRM Laravel Base system...     │
│                                             │
├─────────────────────────────────────────────┤
│ HRM Solutions | https://hrm-laravel-base.example.com │
└─────────────────────────────────────────────┘
```

---

## 🛠️ **Customization Guide**

### **Quick Branding**
```bash
# Change company name
composer run docs-theme "My Company Name"

# This updates config/pdf-theme.json:
# "brand": { "name": "My Company Name", ... }
```

### **Color Customization**
```bash
# Launch interactive configurator
composer run docs-theme-interactive

# Choose option 2 (Theme Colors)
# Follow prompts to customize colors
```

### **Layout Adjustments**
```bash
# Edit configuration directly
nano config/pdf-theme.json

# Or use interactive configurator
composer run docs-theme-interactive
# Choose option 3 (Layout Settings)
```

---

## 📊 **Benefits Over Previous System**

### **Before (Basic PDFs)**
- ❌ No branding or company information
- ❌ Basic black and white styling
- ❌ No headers or footers
- ❌ No table of contents
- ❌ Inconsistent formatting
- ❌ No customization options

### **After (Professional PDFs)**
- ✅ Full company branding and identity
- ✅ Professional color schemes and typography
- ✅ Dynamic headers with document metadata
- ✅ Auto-generated table of contents
- ✅ Consistent, professional formatting
- ✅ Complete customization control
- ✅ Interactive configuration system

---

## 🔧 **Technical Implementation**

### **Architecture**
- **PdfThemeManager**: Core theme management class
- **Configuration System**: JSON-based with validation
- **CLI Integration**: Composer scripts for easy access
- **Template Engine**: Dynamic HTML generation with themes
- **PDF Processing**: Enhanced pandoc/wkhtmltopdf integration

### **Key Classes**
- `PdfThemeManager`: Theme configuration and CSS generation
- `ThemeConfigurator`: Interactive CLI configuration
- Enhanced `DocumentationGenerator`: Integrated theme support

### **File Structure**
```
config/
├── pdf-theme.json              # Main theme configuration
app/Services/
├── PdfThemeManager.php         # Theme management class
scripts/
├── generate-docs-pdf.php       # Enhanced generator
├── configure-pdf-theme.php      # Interactive configurator
```

---

## 🎉 **Summary**

The enhanced PDF documentation system provides:

1. **🏢 Professional Branding** - Complete company identity
2. **🎨 Advanced Theming** - Customizable colors and styles
3. **📋 Rich Features** - TOC, headers, footers, watermarks
4. **⚙️ Easy Configuration** - JSON config + CLI tools
5. **🛠️ Developer Friendly** - Extensible and maintainable
6. **📱 Professional Output** - Print-ready PDFs with proper formatting

**Result**: Documentation that looks like it came from a professional design team, not an auto-generator.

---

*Enhanced PDF System Implementation Complete* ✅  
*Professional Branding and Theming Active* 🎨  
*Ready for Production Documentation* 📄