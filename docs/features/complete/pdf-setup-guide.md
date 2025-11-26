# Simple PDF Documentation Setup Guide

## 🚀 Quick Start

The PDF generation has been simplified for reliability and clean output.

### Available Commands

```bash
# Generate both HTML and PDF (if tools available)
composer run docs-pdf-simple

# Generate with verbose output
composer run docs-pdf-simple --verbose

# Force HTML only (faster)
composer run docs-pdf-simple --force-html

# Complete workflow (test results + PDF generation)
composer run dev-cp-pdf
```

### 📁 Output Location

All generated files are saved to: `docs/pdf/`

- **HTML files**: Clean, web-ready documentation
- **PDF files**: Professional printable documents (if PDF tools available)
- **Index**: `docs/pdf/index.html` - Navigation hub

### 🛠️ PDF Generation Options

The system automatically detects and uses available tools:

1. **DomPDF** (PHP extension) - Preferred
2. **Pandoc** - Command line tool
3. **wkhtmltopdf** - Command line tool

If no PDF tools are available, it generates clean HTML files instead.

### 🎨 Simplified Theme

The new simplified theme provides:
- Clean, professional styling
- Reliable PDF output
- Mobile-responsive HTML
- Fast loading times
- Minimal complexity

### 📋 Features

- ✅ Clean markdown parsing
- ✅ Professional typography
- ✅ Responsive design
- ✅ Code syntax highlighting
- ✅ Table formatting
- ✅ Navigation structure
- ✅ Auto-generated index

### 🔧 Troubleshooting

**PDF generation fails:**
```bash
# Check what tools are available
which pandoc wkhtmltopdf

# Install tools if needed
# Ubuntu/Debian:
sudo apt-get install -y pandoc wkhtmltopdf

# macOS:
brew install pandoc wkhtmltopdf
```

**HTML files look wrong:**
1. Clear browser cache
2. Check file permissions
3. Verify markdown syntax

**Missing files:**
1. Ensure `docs/` directory exists
2. Check file permissions
3. Run with `--verbose` to see debug info

### 📞 Help

```bash
# Show all options
php scripts/generate-docs-pdf-simple.php --help
```

---

*This simplified system provides reliable documentation generation with clean, professional output.*