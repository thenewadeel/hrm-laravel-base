# Documentation PDF Generation Configuration

This directory contains configuration files for the documentation PDF generation system.

## Files

### `docs-pdf.json`
Main configuration file containing:
- **Conversion settings**: Engine selection, timeout, file age limits
- **Styling options**: CSS file, fonts, margins, page size
- **Output settings**: Directory selection, HTML/PDF generation options
- **Branding**: Title, watermark settings
- **Headers**: Configurable document headers with content, styling, and layout
- **Footers**: Configurable document footers with page numbers and dates
- **Features**: Table of contents, syntax highlighting, page breaks

### `docs-pdf-theme.css`
Default CSS theme for generated documentation. Includes:
- Responsive typography with smaller margins (2mm)
- Print-optimized styles
- Code syntax highlighting
- Table of contents styling
- Header/footer layouts with configurable styling
- Watermark support

## Usage

The PDF generation script is called via Composer:
```bash
composer run docs-pdf
```

Or directly:
```bash
php scripts/generate-docs-pdf-simple.php --verbose
```

## Interactive Configuration

Configure themes interactively:
```bash
composer run docs-theme
```

Or directly:
```bash
php scripts/configure-pdf-theme.php
```

## Customization

### Styling
Edit `docs-pdf-theme.css` to change visual appearance. The CSS uses CSS variables that can be overridden in the JSON configuration.

#### Margin Troubleshooting
If margins appear larger than configured:
1. Check `docs-pdf.json` margins are set correctly (e.g., "2mm")
2. Ensure CSS `@page` rule has `!important` declaration
3. Verify PDF tools are receiving margin parameters (check verbose output)
4. Some PDF tools have minimum margin requirements - try 3mm if 2mm doesn't work

### HTML Templates
Edit files in `scripts/templates/` directory to customize layout:
- `docs-html.blade.php` - Main HTML template for web output
- `docs-pdf.blade.php` - HTML template for PDF generation
- `docs-header.blade.php` - Header component with styling
- `docs-footer.blade.php` - Footer component with page numbers
- `docs-nav.blade.php` - Navigation component
- `docs-watermark.blade.php` - Watermark component

### Conversion Parameters
Edit `docs-pdf.json` to customize:
- PDF generation tools (pandoc, wkhtmltopdf)
- Page margins and sizes (now 2mm for better space usage)
- Font families and sizes
- Output formats (HTML, PDF, or both)
- File processing age limits

### Headers and Footers
Configure headers and footers in `docs-pdf.json`:
- Enable/disable headers and footers independently
- Custom content with placeholders ({title}, {brand_title}, {page}, {total}, {date})
- Font sizes, alignment, borders, padding, margins
- Separate styling for HTML and PDF output

### Branding
Update the branding section in `docs-pdf.json` to customize:
- Document title
- Watermark text (PDF only)
- Date formatting

## Features

- **Multi-format output**: Generate both HTML and PDF formats
- **Responsive design**: HTML files work on desktop and mobile
- **Print optimization**: CSS optimized for high-quality PDF output
- **Navigation**: Automatic table of contents and navigation
- **Customizable styling**: Separate CSS and JSON configuration
- **Tool detection**: Automatically uses available PDF generation tools
- **Incremental processing**: Only processes recently changed files