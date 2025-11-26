# Documentation Generation Scripts

This directory contains scripts and templates for generating documentation from markdown files.

## Files

### `generate-docs-pdf-simple.php`
Main script for converting markdown files to HTML and PDF formats. Features:
- Template-based HTML generation
- Configurable headers and footers
- Multiple PDF engine support (pandoc, wkhtmltopdf)
- Incremental processing (only recent files)
- JSON configuration

### `templates/`
Directory containing HTML templates for document generation:

#### `docs-html.blade.php`
Main template for HTML output (web viewing)
- Includes header, navigation, content, footer
- Responsive design with CSS styling

#### `docs-pdf.blade.php`
Template for PDF generation
- Optimized for print/PDF output
- Includes watermark support

#### `docs-header.blade.php`
Header component template
- Configurable content and styling
- Supports placeholders: {title}, {brand_title}

#### `docs-footer.blade.php`
Footer component template
- Page numbering and date stamps
- Supports placeholders: {page}, {total}, {date}

#### `docs-nav.blade.php`
Navigation component template
- Auto-generated table of contents
- Links to related documentation

#### `docs-watermark.blade.php`
Watermark component for PDFs
- Diagonal text overlay
- Configurable watermark text

## Usage

```bash
# Generate documentation
php generate-docs-pdf-simple.php --verbose

# Show help
php generate-docs-pdf-simple.php --help

# Via Composer
composer run docs-pdf
```

## Configuration

All configuration is handled through:
- `config/docs-pdf.json` - Main settings
- `config/docs-pdf-theme.css` - Styling

## Template Customization

Templates use simple placeholder replacement:
- `{title}` - Document title
- `{brand_title}` - Brand title from config
- `{content}` - Main document content
- `{css}` - CSS styles
- `{page}` - Current page number
- `{total}` - Total page count
- `{date}` - Current date/time

Edit template files to customize layout and structure without touching the main script.