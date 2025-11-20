<?php


namespace App\Services;

/**
 * PDF Theme Manager
 *
 * Handles PDF branding, styling, and customization
 */
class PdfThemeManager
{
    private array $config;
    private string $configPath;

    public function __construct(string $configPath = null)
    {
        $this->configPath = $configPath ?: __DIR__ . '/../../config/pdf-theme.json';
        $this->loadConfig();
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getBrand(): array
    {
        return isset($this->config['brand']) ? $this->config['brand'] : [];
    }

    public function getTheme(): array
    {
        return isset($this->config['theme']) ? $this->config['theme'] : [];
    }

    public function getLayout(): array
    {
        return isset($this->config['layout']) ? $this->config['layout'] : [];
    }

    public function getFeatures(): array
    {
        return isset($this->config['features']) ? $this->config['features'] : [];
    }

    public function getCustom(): array
    {
        return isset($this->config['custom']) ? $this->config['custom'] : [];
    }

    public function getCss(): string
    {
        $brand = $this->getBrand();
        $theme = $this->getTheme();
        $layout = $this->getLayout();
        $features = $this->getFeatures();
        $custom = $this->getCustom();

        return $this->generateCss($brand, $theme, $layout, $features, $custom);
    }

    public function getHeaderHtml(string $title, int $pageNumber): string
    {
        $brand = $this->getBrand();
        $layout = $this->getLayout();
        $features = $this->getFeatures();
        $custom = $this->getCustom();

        if (!$features['auto_toc'] && $pageNumber > 1) {
            return '';
        }

        $logo = $brand['logo'] ?? '🏢';
        $name = $brand['name'] ?? 'Documentation';
        $tagline = $brand['tagline'] ?? '';
        $date = date('Y-m-d');
        $version = $brand['version'] ?? '';

        $headerStyle = $this->getHeaderStyle();

        return <<<HTML
<div class="pdf-header {$headerStyle}">
    <div class="header-left">
        <div class="logo">{$logo}</div>
        <div class="brand-info">
            <div class="brand-name">{$name}</div>
            <div class="brand-tagline">{$tagline}</div>
        </div>
    </div>
    <div class="header-right">
        <div class="document-info">
            <div class="document-title">{$title}</div>
            <div class="document-meta">
                <span class="version">v{$version}</span>
                <span class="date">{$date}</span>
                <span class="page">Page {$pageNumber}</span>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    public function getFooterHtml(int $pageNumber, int $totalPages): string
    {
        $brand = $this->getBrand();
        $features = $this->getFeatures();
        $custom = $this->getCustom();

        if (!$features['page_numbers']) {
            return '';
        }

        $company = $brand['company'] ?? '';
        $website = $brand['website'] ?? '';
        $copyright = $brand['copyright'] ?? '';
        $confidential = $custom['confidential_text'] ?? '';

        $footerStyle = $this->getFooterStyle();

        $footerContent = '';

        if ($confidential) {
            $footerContent .= "<div class=\"confidential\">{$confidential}</div>";
        }

        $footerContent .= <<<HTML
<div class="pdf-footer {$footerStyle}">
    <div class="footer-left">
        <div class="company">{$company}</div>
        <div class="copyright">{$copyright}</div>
    </div>
    <div class="footer-right">
        <div class="page-info">Page {$pageNumber} of {$totalPages}</div>
        <div class="website">{$website}</div>
    </div>
</div>
HTML;

        return $footerContent;
    }

    public function getTableOfContentsHtml(array $headings): string
    {
        $theme = $this->getTheme();
        $features = $this->getFeatures();

        if (!$features['auto_toc'] || empty($headings)) {
            return '';
        }

        $tocStyle = $this->getTocStyle();
        $tocHtml = '<div class="table-of-contents ' . $tocStyle . '">';
        $tocHtml .= '<h2 class="toc-title">📋 Table of Contents</h2>';

        foreach ($headings as $heading) {
            $level = $heading['level'] ?? 1;
            $title = $heading['title'] ?? '';
            $anchor = $heading['anchor'] ?? '';
            $indent = $level > 1 ? str_repeat('  ', $level - 1) : '';
            $pageNumber = $heading['page'] ?? '';

            $tocHtml .= <<<HTML
<div class="toc-item toc-level-{$level}">
    <a href="#{$anchor}" class="toc-link">{$indent}{$title}</a>
    <span class="toc-page-number">{$pageNumber}</span>
</div>
HTML;
        }

        $tocHtml .= '</div>';

        return $tocHtml;
    }

    private function loadConfig(): void
    {
        if (file_exists($this->configPath)) {
            $content = file_get_contents($this->configPath);
            $this->config = json_decode($content, true) ?? [];
        } else {
            $this->config = $this->getDefaultConfig();
            $this->saveConfig();
        }
    }

    private function saveConfig(): void
    {
        $json = json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($this->configPath, $json);
    }

    private function getDefaultConfig(): array
    {
        return [
            'brand' => [
                'name' => 'HRM Laravel Base',
                'tagline' => 'Enterprise ERP System',
                'logo' => '🏢',
                'website' => 'https://hrm-laravel-base.example.com',
                'company' => 'HRM Solutions',
                'version' => '2.0.0'
            ],
            'theme' => [
                'primary_color' => '#2563eb',
                'secondary_color' => '#64748b',
                'accent_color' => '#3b82f6',
                'success_color' => '#10b981',
                'warning_color' => '#f59e0b',
                'error_color' => '#ef4444',
                'background_color' => '#ffffff',
                'text_color' => '#1f2937',
                'border_color' => '#e5e7eb',
                'header_font' => 'Arial, sans-serif',
                'body_font' => 'Georgia, serif',
                'code_font' => "'Courier New', monospace"
            ],
            'custom' => [
                'watermark_text' => '',
                'confidential_text' => '',
                'draft_mode' => false,
                'print_date' => true,
                'author_info' => true
            ]
        ];
    }

    private function generateCss(array $brand, array $theme, array $layout, array $features, array $custom): string
    {
        $headerStyle = $this->getHeaderStyle();
        $footerStyle = $this->getFooterStyle();
        $tocStyle = $this->getTocStyle();

        // Extract all variables with defaults to avoid null coalescing in heredoc
        $bodyFont = $layout['body_font'] ?? 'Georgia, serif';
        $fontSize = $layout['font_size'] ?? '11pt';
        $lineHeight = $layout['line_height'] ?? '1.6';
        $textColor = $theme['text_color'] ?? '#1f2937';
        $backgroundColor = $theme['background_color'] ?? '#ffffff';
        $marginTop = $layout['margin_top'] ?? '20mm';
        $marginRight = $layout['margin_right'] ?? '20mm';
        $marginBottom = $layout['margin_bottom'] ?? '20mm';
        $marginLeft = $layout['margin_left'] ?? '20mm';
        $headerFont = $layout['header_font'] ?? 'Arial, sans-serif';
        $primaryColor = $theme['primary_color'] ?? '#2563eb';
        $secondaryColor = $theme['secondary_color'] ?? '#64748b';
        $accentColor = $theme['accent_color'] ?? '#3b82f6';
        $borderColor = $theme['border_color'] ?? '#e5e7eb';
        $codeFont = $layout['code_font'] ?? "'Courier New', monospace";
        $headerHeight = $layout['header_height'] ?? '15mm';
        $footerHeight = $layout['footer_height'] ?? '15mm';
        $successColor = $theme['success_color'] ?? '#10b981';
        $errorColor = $theme['error_color'] ?? '#ef4444';

        return <<<CSS
/* ===== PDF STYLES ===== */

/* Base Styles */
body {
    font-family: {$bodyFont};
    font-size: {$fontSize};
    line-height: {$lineHeight};
    color: {$textColor};
    background-color: {$backgroundColor};
    margin: 0;
    padding: {$marginTop} {$marginRight} {$marginBottom} {$marginLeft};
}
.document {
    max-width: 100%;
    margin: 0 auto;
}

/* Typography */
h1, h2, h3, h4, h5, h6 {
    font-family: {$headerFont};
    color: {$primaryColor};
    font-weight: 600;
    line-height: 1.3;
    page-break-after: avoid;
    margin-top: 24px;
    margin-bottom: 16px;
}

h1 {
    font-size: 28pt;
    border-bottom: 3px solid {$primaryColor};
    padding-bottom: 12px;
    margin-top: 0;
    background: linear-gradient(135deg, {$primaryColor}20 0%, transparent 100%);
    padding: 20px;
    margin: 0 0 20px 0;
    border-radius: 8px;
}

h2 {
    font-size: 20pt;
    border-bottom: 2px solid {$borderColor};
    padding-bottom: 8px;
    background: {$backgroundColor};
    padding-left: 10px;
    border-left: 4px solid {$accentColor};
}

h3 {
    font-size: 16pt;
    color: {$secondaryColor};
}

h4 {
    font-size: 14pt;
    color: {$secondaryColor};
}

h5, h6 {
    font-size: 12pt;
    color: {$secondaryColor};
}

p {
    margin-bottom: 12px;
    text-align: justify;
    orphans: 3;
    widows: 3;
}

/* Links */
a {
    color: {$primaryColor};
    border-bottom: 1px solid transparent;
    transition: all 0.2s ease;
}

a:hover {
    border-bottom-color: {$primaryColor};
}

/* Code Blocks */
code {
    font-family: {$codeFont};
    background-color: #f8f9fa;
    color: #e11d48;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 10pt;
    border: 1px solid {$borderColor};
}

pre {
    background-color: #1e293b;
    color: #e2e8f0;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 16px;
    overflow-x: auto;
    margin: 16px 0;
    page-break-inside: avoid;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

pre code {
    background-color: transparent;
    color: inherit;
    padding: 0;
    border: none;
    font-size: 10pt;
}

/* Blockquotes */
blockquote {
    border-left: 4px solid {$primaryColor};
    margin: 16px 0;
    padding: 12px 20px;
    background-color: #f8fafc;
    color: #475569;
    font-style: italic;
    border-radius: 0 8px 8px 0;
}

/* Tables */
table {
    border-collapse: collapse;
    width: 100%;
    margin: 16px 0;
    page-break-inside: avoid;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

th, td {
    border: 1px solid {$borderColor};
    padding: 8px 12px;
    text-align: left;
}

th {
    background-color: {$primaryColor};
    color: white;
    font-weight: 600;
}

tr:nth-child(even) {
    background-color: #f8fafc;
}

/* Lists */
ul, ol {
    margin: 12px 0;
    padding-left: 30px;
}

li {
    margin-bottom: 6px;
}

/* Horizontal Rule */
hr {
    border: none;
    border-top: 2px solid {$borderColor};
    margin: 24px 0;
}

/* ===== HEADER STYLES ===== */

.pdf-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: {$headerHeight};
    padding: 0 {$marginLeft} 0 {$marginRight};
    border-bottom: 1px solid {$borderColor};
    background: linear-gradient(135deg, {$primaryColor}10 0%, {$backgroundColor} 100%);
    z-index: 1000;
}

.header-left {
    float: left;
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo {
    font-size: 24px;
    color: {$primaryColor};
}

.brand-info {
    display: flex;
    flex-direction: column;
}

.brand-name {
    font-family: {$headerFont};
    font-size: 14px;
    font-weight: 600;
    color: {$primaryColor};
}

.brand-tagline {
    font-family: {$headerFont};
    font-size: 10px;
    color: {$secondaryColor};
    font-style: italic;
}

.header-right {
    float: right;
    text-align: right;
}

.document-title {
    font-family: {$headerFont};
    font-size: 12px;
    font-weight: 600;
    color: {$primaryColor};
    margin-bottom: 4px;
}

.document-meta {
    display: flex;
    gap: 12px;
    font-family: {$headerFont};
    font-size: 9px;
    color: {$secondaryColor};
}

.version {
    background: {$successColor};
    color: white;
    padding: 2px 6px;
    border-radius: 12px;
    font-weight: 500;
}

.date {
    background: {$accentColor};
    color: white;
    padding: 2px 6px;
    border-radius: 12px;
    font-weight: 500;
}

.page {
    background: {$secondaryColor};
    color: white;
    padding: 2px 6px;
    border-radius: 12px;
    font-weight: 500;
}

/* ===== FOOTER STYLES ===== */

.pdf-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: {$footerHeight};
    padding: 0 {$marginLeft} 0 {$marginRight};
    border-top: 1px solid {$borderColor};
    background: linear-gradient(135deg, {$backgroundColor} 0%, {$primaryColor}10 100%);
    z-index: 1000;
}

.footer-left {
    float: left;
    font-family: {$headerFont};
    font-size: 9px;
    color: {$secondaryColor};
}

.company {
    font-weight: 600;
    color: {$primaryColor};
}

.copyright {
    font-style: italic;
}

.footer-right {
    float: right;
    text-align: right;
    font-family: {$headerFont};
    font-size: 9px;
    color: {$secondaryColor};
}

.page-info {
    font-weight: 600;
    color: {$primaryColor};
}

.website {
    color: {$primaryColor};
}

/* ===== TABLE OF CONTENTS ===== */

.table-of-contents {
    background: #f8fafc;
    border: 1px solid {$borderColor};
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    page-break-after: always;
}

.toc-title {
    font-family: {$headerFont};
    font-size: 18px;
    color: {$primaryColor};
    margin: 0 0 16px 0;
    text-align: center;
    border-bottom: 2px solid {$borderColor};
    padding-bottom: 8px;
}

.toc-item {
    margin: 4px 0;
    padding: 4px 0;
}

.toc-level-1 {
    font-weight: 600;
}

.toc-level-2 {
    margin-left: 20px;
    font-weight: 400;
}

.toc-level-3 {
    margin-left: 40px;
    font-weight: 400;
    font-size: 0.9em;
}

.toc-link {
    color: {$primaryColor};
    text-decoration: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toc-link:hover {
    text-decoration: underline;
}

.toc-page-number {
    color: {$secondaryColor};
    font-size: 0.8em;
    background: {$borderColor};
    padding: 2px 6px;
    border-radius: 10px;
}

/* ===== SPECIAL STYLES ===== */

.confidential {
    background: {$errorColor};
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 8px;
}

/* ===== PRINT STYLES ===== */

@media print {
    body {
        margin: 0;
        padding: 0;
    }

    .document {
        margin: 0;
        padding: 0;
    }

    h1, h2, h3, h4 {
        page-break-after: avoid;
    }

    p, ul, ol {
        orphans: 3;
        widows: 3;
    }

    pre, blockquote, table {
        page-break-inside: avoid;
    }

    .table-of-contents {
        page-break-after: always;
    }
}

CSS;
    }

    private function getHeaderStyle(): string
    {
        $styling = $this->config['styling'] ?? [];
        return $styling['header_style'] ?? 'modern';
    }

    private function getFooterStyle(): string
    {
        $styling = $this->config['styling'] ?? [];
        return $styling['footer_style'] ?? 'modern';
    }

    private function getTocStyle(): string
    {
        $styling = $this->config['styling'] ?? [];
        return $styling['toc_style'] ?? 'numbered';
    }
}
