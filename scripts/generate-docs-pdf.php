#!/usr/bin/env php
<?php

/**
 * Documentation PDF/HTML Generator
 * 
 * Converts markdown files to PDF (if tools available) or HTML format
 * for better distribution and offline reading.
 */

require_once __DIR__ . '/../app/Services/PdfThemeManager.php';

class DocumentationGenerator
{
    private string $docsDir;
    private string $outputDir;
    private bool $verbose = false;
    private bool $forceHtml = false;
    private bool $cleanupHtml = false;
    private int $maxAgeHours = 24; // Only process files changed in last 24 hours

    public function __construct()
    {
        $this->docsDir = __DIR__ . '/../docs';
        $this->outputDir = $this->docsDir . '/pdf';
        $this->ensureDirectoryExists($this->outputDir);
    }

    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    public function setForceHtml(bool $force): void
    {
        $this->forceHtml = $force;
    }

    public function setCleanupHtml(bool $cleanup): void
    {
        $this->cleanupHtml = $cleanup;
    }

    public function setMaxAgeHours(int $hours): void
    {
        $this->maxAgeHours = $hours;
    }

    public function generate(): void
    {
        $this->log("🚀 Starting documentation generation...");
        
        // Find recently changed markdown files
        $mdFiles = $this->findRecentlyChangedMarkdownFiles();
        
        if (empty($mdFiles)) {
            $this->log("✅ No recently changed markdown files found. Nothing to process.");
            return;
        }

        $this->log("📄 Found " . count($mdFiles) . " recently changed markdown files to process.");

        // Check available tools
        $hasPandoc = $this->commandExists('pandoc');
        $hasWkHtml = $this->commandExists('wkhtmltopdf');
        
        if (!$hasPandoc && !$hasWkHtml) {
            $this->log("⚠️  PDF tools not found. Installing...");
            $this->installPdfTools();
            $hasPandoc = $this->commandExists('pandoc');
            $hasWkHtml = $this->commandExists('wkhtmltopdf');
        }

        if (!$hasPandoc && !$hasWkHtml) {
            $this->log("⚠️  PDF tools still not available. Generating HTML files instead.");
            $this->log("💡 To enable PDF generation, install: pandoc and/or wkhtmltopdf");
            $this->forceHtml = true;
        }

        // Process each file
        $successCount = 0;
        $errorCount = 0;

        foreach ($mdFiles as $mdFile) {
            try {
                $this->convertFile($mdFile, $hasPandoc, $hasWkHtml);
                $successCount++;
                $this->log("✅ Converted: " . $this->getRelativePath($mdFile));
            } catch (Exception $e) {
                $errorCount++;
                $this->log("❌ Failed to convert " . $this->getRelativePath($mdFile) . ": " . $e->getMessage());
            }
        }

        $this->log("\n📊 Generation Summary:");
        $this->log("✅ Successfully converted: {$successCount} files");
        $this->log("❌ Failed conversions: {$errorCount} files");
        $this->log("📁 Files saved to: " . $this->outputDir);

        // Generate index and navigation
        $this->generateIndex();
        $this->generateNavigation();
        
        // Create installation guide
        if (!$hasPandoc && !$hasWkHtml) {
            $this->createInstallationGuide();
        }

        // Clean up intermediate HTML files if requested
        if ($this->cleanupHtml) {
            $this->cleanupIntermediateHtmlFiles();
        }
    }

    private function findRecentlyChangedMarkdownFiles(): array
    {
        $mdFiles = [];
        $cutoffTime = time() - ($this->maxAgeHours * 3600);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->docsDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                // Skip files in output directory
                if (strpos($file->getPathname(), $this->outputDir) === false) {
                    $modTime = $file->getMTime();
                    
                    // Include file if it was recently modified
                    if ($modTime >= $cutoffTime) {
                        $mdFiles[] = $file->getPathname();
                        $this->log("📝 Recently changed: " . $this->getRelativePath($file->getPathname()) . 
                                 " (modified: " . date('Y-m-d H:i:s', $modTime) . ")");
                    }
                }
            }
        }

        // If no recently changed files, check if we should process all files (first run)
        if (empty($mdFiles)) {
            $this->log("ℹ️  No recently changed files found. Checking if this is first run...");
            if (!$this->hasExistingOutput()) {
                $this->log("🆕 First run detected. Processing all markdown files...");
                return $this->findMarkdownFiles();
            }
        }

        return $mdFiles;
    }

    private function findMarkdownFiles(): array
    {
        $mdFiles = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->docsDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                // Skip files in output directory
                if (strpos($file->getPathname(), $this->outputDir) === false) {
                    $mdFiles[] = $file->getPathname();
                }
            }
        }

        return $mdFiles;
    }

    private function hasExistingOutput(): bool
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->outputDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['html', 'pdf'])) {
                return true;
            }
        }

        return false;
    }

    private function cleanupIntermediateHtmlFiles(): void
    {
        $this->log("🧹 Cleaning up intermediate HTML files...");
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->outputDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $cleanedCount = 0;
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'html') {
                // Keep index.html and README.md
                if (!in_array($file->getBasename(), ['index.html', 'README.md'])) {
                    unlink($file->getPathname());
                    $cleanedCount++;
                    $this->log("🗑️  Removed: " . $file->getBasename());
                }
            }
        }

        $this->log("✅ Cleaned up {$cleanedCount} intermediate HTML files");
    }

    private function convertFile(string $mdFile, bool $hasPandoc, bool $hasWkHtml): void
    {
        $relativePath = $this->getRelativePath($mdFile);
        $baseName = basename($relativePath, '.md');
        
        // Create output path maintaining directory structure
        $outputPath = $this->outputDir . '/' . str_replace('.md', '', $relativePath);
        $outputDir = dirname($outputPath);
        
        if ($outputDir !== $this->outputDir) {
            $this->ensureDirectoryExists($outputDir);
        }

        if ($this->forceHtml || (!$hasPandoc && !$hasWkHtml)) {
            // Generate HTML
            $htmlPath = $outputPath . '.html';
            $this->createHtmlFile($mdFile, $htmlPath, $baseName);
        } else {
            // Try PDF generation
            $pdfPath = $outputPath . '.pdf';
            
            if ($hasPandoc) {
                $this->convertWithPandoc($mdFile, $pdfPath);
            } elseif ($hasWkHtml) {
                $this->convertWithWkHtml($mdFile, $pdfPath);
            }
            
            // Also generate HTML for web viewing
            $htmlPath = $outputPath . '.html';
            $this->createHtmlFile($mdFile, $htmlPath, $baseName);
        }
    }

    private function createHtmlFile(string $mdFile, string $htmlPath, string $title): void
    {
        $markdown = file_get_contents($mdFile);
        $html = $this->markdownToHtml($markdown);
        $htmlWithLayout = $this->wrapHtmlWithLayout($html, $title, $this->getRelativePath($mdFile));
        
        file_put_contents($htmlPath, $htmlWithLayout);
    }

    private function convertWithPandoc(string $mdFile, string $pdfPath): void
    {
        $theme = new PdfThemeManager();
        $themeConfig = $theme->getConfig();
        $layout = $theme->getLayout();
        
        // Create enhanced HTML with theme
        $markdown = file_get_contents($mdFile);
        $html = $this->markdownToHtml($markdown);
        $headings = $this->extractHeadings($html);
        
        // Build complete HTML document
        $fullHtml = $this->buildThemedHtml($html, $headings, $mdFile, $theme);
        
        // Write to temporary files
        $tempHtml = tempnam(sys_get_temp_dir(), 'pdf_html_') . '.html';
        $tempCss = tempnam(sys_get_temp_dir(), 'pdf_css_') . '.css';
        
        file_put_contents($tempHtml, $fullHtml);
        file_put_contents($tempCss, $theme->getCss());

        $process = new Process([
            'pandoc',
            '-f', 'html',
            '-t', 'pdf',
            '--pdf-engine=wkhtmltopdf',
            '--css', $tempCss,
            '-V', 'geometry:margin=' . $layout['margin_left'] . 'mm',
            '-V', 'fontsize=' . $layout['font_size'],
            '-V', 'fontfamily=' . $themeConfig['theme']['body_font'],
            '--highlight-style=' . ($themeConfig['theme']['code_theme'] ?? 'pygments'),
            '-o', $pdfPath,
            $tempHtml
        ]);

        $process->run();
        unlink($tempHtml);
        unlink($tempCss);

        if (!$process->isSuccessful()) {
            throw new Exception("Pandoc conversion failed: " . $process->getErrorOutput());
        }
    }

    private function convertWithWkHtml(string $mdFile, string $pdfPath): void
    {
        $theme = new PdfThemeManager();
        $markdown = file_get_contents($mdFile);
        $html = $this->markdownToHtml($markdown);
        $headings = $this->extractHeadings($html);
        
        // Build themed HTML
        $fullHtml = $this->buildThemedHtml($html, $headings, $mdFile, $theme);
        
        $tempHtml = tempnam(sys_get_temp_dir(), 'pdf_html_') . '.html';
        file_put_contents($tempHtml, $fullHtml);

        $layout = $theme->getLayout();
        
        $process = new Process([
            'wkhtmltopdf',
            '--page-size', $layout['page_size'],
            '--margin-top', $layout['margin_top'],
            '--margin-right', $layout['margin_right'],
            '--margin-bottom', $layout['margin_bottom'],
            '--margin-left', $layout['margin_left'],
            '--encoding', 'UTF-8',
            '--print-media-type',
            '--enable-local-file-access',
            '--header-html', $this->createHeaderHtml($tempHtml, $theme),
            '--header-spacing', $layout['header_spacing'],
            '--footer-html', $this->createFooterHtml($tempHtml, $theme),
            '--footer-spacing', $layout['footer_spacing'],
            $tempHtml,
            $pdfPath
        ]);

        $process->run();
        unlink($tempHtml);

        if (!$process->isSuccessful()) {
            throw new Exception("wkhtmltopdf conversion failed: " . $process->getErrorOutput());
        }
    }

    private function createHeaderHtml(string $htmlFile, PdfThemeManager $theme): string
    {
        $title = basename($htmlFile, '.html');
        return $theme->getHeaderHtml($title, 1);
    }

    private function createFooterHtml(string $htmlFile, PdfThemeManager $theme): string
    {
        return $theme->getFooterHtml(1, 999); // Will be updated by wkhtmltopdf
    }

    private function markdownToHtml(string $markdown): string
    {
        // Enhanced markdown to HTML conversion
        $html = $markdown;
        
        // Code blocks with language
        $html = preg_replace('/```(\w+)?\n(.*?)\n```/s', '<pre class="code-block language-$1"><code>$2</code></pre>', $html);
        
        // Inline code
        $html = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $html);
        
        // Headers
        $html = preg_replace('/^# (.+)$/m', '<h1 id="$1">$1</h1>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2 id="$1">$1</h2>', $html);
        $html = preg_replace('/^### (.+)$/m', '<h3 id="$1">$1</h3>', $html);
        $html = preg_replace('/^#### (.+)$/m', '<h4 id="$1">$1</h4>', $html);
        
        // Bold and italic
        $html = preg_replace('/\*\*\*([^*]+)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $html);
        
        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);
        
        // Images
        $html = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" style="max-width: 100%; height: auto;">', $html);
        
        // Blockquotes
        $html = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $html);
        
        // Horizontal rules
        $html = preg_replace('/^---$/m', '<hr>', $html);
        
        // Line breaks
        $html = str_replace("\n\n", "</p>\n<p>", $html);
        $html = "<p>" . $html . "</p>";
        $html = str_replace(["\n", "<p></p>"], ["<br>", ""], $html);
        
        // Lists
        $html = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
        
        return $html;
    }

    private function wrapHtmlWithLayout(string $content, string $title, string $relativePath): string
    {
        $css = $this->getLayoutCss();
        $nav = $this->generateNavigationHtml($relativePath);
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - HRM Laravel Base Documentation</title>
    <style>
{$css}
    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <div class="nav-header">
                <h2>📚 Documentation</h2>
            </div>
            {$nav}
        </nav>
        <main class="content">
            <div class="document">
                {$content}
            </div>
        </main>
    </div>
    <script>
        // Simple navigation and search
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight current page in nav
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
HTML;
    }

    private function wrapHtmlWithCss(string $html): string
    {
        $css = $this->getPdfCss();
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Documentation</title>
    <style>
{$css}
    </style>
</head>
<body>
    <div class="document">
        {$html}
    </div>
</body>
</html>
HTML;
    }

    private function getLayoutCss(): string
    {
        return <<<CSS
* {
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8fafc;
    color: #334155;
    line-height: 1.6;
}

.container {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 280px;
    background: white;
    border-right: 1px solid #e2e8f0;
    overflow-y: auto;
    position: fixed;
    height: 100vh;
    left: 0;
    top: 0;
}

.nav-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.nav-header h2 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.nav-section {
    padding: 1rem 0;
}

.nav-section-title {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #64748b;
    padding: 0 1.5rem;
    margin-bottom: 0.5rem;
}

.nav-link {
    display: block;
    padding: 0.5rem 1.5rem;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.nav-link:hover {
    background-color: #f1f5f9;
    color: #1e293b;
    border-left-color: #3b82f6;
}

.nav-link.active {
    background-color: #dbeafe;
    color: #1d4ed8;
    border-left-color: #2563eb;
    font-weight: 500;
}

.content {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    max-width: 1200px;
}

.document {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

h1, h2, h3, h4, h5, h6 {
    color: #1e293b;
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
    line-height: 1.25;
}

h1 {
    font-size: 2.5rem;
    border-bottom: 3px solid #3b82f6;
    padding-bottom: 0.5rem;
    margin-top: 0;
}

h2 {
    font-size: 2rem;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.5rem;
}

h3 {
    font-size: 1.5rem;
}

h4 {
    font-size: 1.25rem;
}

p {
    margin-bottom: 1rem;
    text-align: justify;
}

code.inline-code {
    background-color: #f1f5f9;
    color: #e11d48;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, monospace;
    border: 1px solid #e2e8f0;
}

pre.code-block {
    background-color: #1e293b;
    color: #e2e8f0;
    border-radius: 0.5rem;
    padding: 1.5rem;
    overflow-x: auto;
    margin: 1.5rem 0;
    border: 1px solid #334155;
}

pre.code-block code {
    background: none;
    color: inherit;
    padding: 0;
    border-radius: 0;
    font-size: 0.875rem;
}

a {
    color: #3b82f6;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.2s ease;
}

a:hover {
    border-bottom-color: #3b82f6;
}

blockquote {
    border-left: 4px solid #3b82f6;
    margin: 1.5rem 0;
    padding: 1rem 1.5rem;
    background-color: #f8fafc;
    color: #475569;
    font-style: italic;
}

table {
    border-collapse: collapse;
    width: 100%;
    margin: 1.5rem 0;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

th, td {
    border: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
    text-align: left;
}

th {
    background-color: #f8fafc;
    font-weight: 600;
    color: #374151;
}

hr {
    border: none;
    border-top: 2px solid #e2e8f0;
    margin: 2rem 0;
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    .content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .document {
        padding: 1rem;
    }
    
    h1 {
        font-size: 2rem;
    }
    
    h2 {
        font-size: 1.5rem;
    }
}
CSS;
    }

    private function getPdfCss(): string
    {
        return <<<CSS
body {
    font-family: 'Times New Roman', serif;
    font-size: 11pt;
    line-height: 1.6;
    color: #333;
    margin: 0;
    padding: 20px;
}

.document {
    max-width: 100%;
    margin: 0 auto;
}

h1, h2, h3, h4, h5, h6 {
    color: #2c3e50;
    margin-top: 24px;
    margin-bottom: 16px;
    font-weight: 600;
    line-height: 1.25;
    page-break-after: avoid;
}

h1 {
    font-size: 24pt;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
    margin-top: 0;
}

h2 {
    font-size: 18pt;
    border-bottom: 1px solid #bdc3c7;
    padding-bottom: 5px;
}

h3 {
    font-size: 14pt;
}

h4 {
    font-size: 12pt;
}

p {
    margin-bottom: 12px;
    text-align: justify;
    orphans: 3;
    widows: 3;
}

code {
    font-family: 'Courier New', monospace;
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 10pt;
    border: 1px solid #e9ecef;
}

pre {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    padding: 12px;
    overflow-x: auto;
    margin: 16px 0;
    page-break-inside: avoid;
}

pre code {
    background-color: transparent;
    padding: 0;
    border-radius: 0;
    font-size: 10pt;
    border: none;
}

ul, ol {
    margin: 12px 0;
    padding-left: 30px;
}

li {
    margin-bottom: 6px;
}

a {
    color: #3498db;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

blockquote {
    border-left: 4px solid #3498db;
    margin: 16px 0;
    padding: 12px 20px;
    background-color: #f8fafc;
    color: #495057;
    font-style: italic;
}

table {
    border-collapse: collapse;
    width: 100%;
    margin: 16px 0;
    page-break-inside: avoid;
}

th, td {
    border: 1px solid #ddd;
    padding: 8px 12px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
    font-weight: 600;
}

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
}
CSS;
    }

    private function generateNavigationHtml(string $currentPath): string
    {
        $navSections = [
            'Core Documentation' => [
                'big picture.md' => '📊 Big Picture',
                'SRS.md' => '📋 Software Requirements',
                'ERD.md' => '🗄️ Database Design',
                'AGENTS.md' => '🤖 Development Guidelines',
            ],
            'Project Management' => [
                'project plan.md' => '📅 Project Plan',
                'timeline.md' => '⏰ Timeline',
                'progress-report.md' => '📈 Progress Report',
                'project log.md' => '📝 Project Log',
            ],
            'Technical Documentation' => [
                'interfaces spec.md' => '🔌 Interface Specifications',
                'list of modules.md' => '🧩 Module List',
                'list of routes.md' => '🛣️ Route List',
                'list of screens.md' => '🖥️ Screen List',
                'list of interfaces.md' => '🔗 Interface List',
                'list of reqs.md' => '📄 Requirements List',
                'workflows.md' => '⚙️ Workflows',
                'use cases.md' => '🎯 Use Cases',
                'ui dev.md' => '🎨 UI Development',
                'production-database-setup.md' => '🗄️ Database Setup',
            ],
            'Issues & Evolution' => [
                'issues/1-critical-structural-issues.md' => '🚨 Critical Issues',
                'issues/1-implementation-report.md' => '📊 Implementation Report',
                'erp-evolution.md' => '🔄 ERP Evolution',
            ],
        ];

        $navHtml = '';
        
        foreach ($navSections as $sectionTitle => $files) {
            $navHtml .= '<div class="nav-section">';
            $navHtml .= '<div class="nav-section-title">' . $sectionTitle . '</div>';
            
            foreach ($files as $file => $title) {
                $href = str_replace('.md', '.html', $file);
                $active = $currentPath === $file ? 'active' : '';
                $navHtml .= "<a href=\"{$href}\" class=\"nav-link {$active}\">{$title}</a>";
            }
            
            $navHtml .= '</div>';
        }

        return $navHtml;
    }

    private function generateNavigation(): void
    {
        // This is already handled in wrapHtmlWithLayout
    }

    private function generateIndex(): void
    {
        $indexContent = "# Documentation Portal\n\n";
        $indexContent .= "**Generated on:** " . date('Y-m-d H:i:s') . "\n\n";
        $indexContent .= "## 📚 Available Documentation\n\n";
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->outputDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $files = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['html', 'pdf'])) {
                $relativePath = str_replace($this->outputDir . '/', '', $file->getPathname());
                $name = basename($file->getPathname(), '.' . $file->getExtension());
                $files[] = [
                    'path' => $relativePath,
                    'name' => $name,
                    'type' => $file->getExtension()
                ];
            }
        }

        sort($files);

        foreach ($files as $file) {
            $icon = $file['type'] === 'pdf' ? '📄' : '🌐';
            $indexContent .= "- {$icon} [{$file['name']}]({$file['path']})\n";
        }

        $indexContent .= "\n---\n\n";
        $indexContent .= "### 📖 Navigation\n\n";
        $indexContent .= "- [🏠 Home Index](index.html)\n";
        $indexContent .= "- [📊 Big Picture](big-picture.html)\n";
        $indexContent .= "- [🤖 Development Guidelines](AGENTS.html)\n";
        $indexContent .= "- [📋 Software Requirements](SRS.html)\n";

        file_put_contents($this->outputDir . '/README.md', $indexContent);
        
        // Also create HTML index
        $this->createHtmlFile($this->outputDir . '/README.md', $this->outputDir . '/index.html', 'Documentation Portal');
        
        $this->log("📋 Generated index files");
    }

    private function createInstallationGuide(): void
    {
        $guide = "# PDF Generation Setup Guide\n\n";
        $guide .= "## 📋 Required Tools\n\n";
        $guide .= "To generate PDF documentation, install one or both of these tools:\n\n";
        $guide .= "### Option 1: Pandoc (Recommended)\n\n";
        $guide .= "```bash\n";
        $guide .= "# Ubuntu/Debian\n";
        $guide .= "sudo apt-get update\n";
        $guide .= "sudo apt-get install -y pandoc wkhtmltopdf\n\n";
        $guide .= "# macOS\n";
        $guide .= "brew install pandoc wkhtmltopdf\n\n";
        $guide .= "# Windows (using Chocolatey)\n";
        $guide .= "choco install pandoc wkhtmltopdf\n";
        $guide .= "```\n\n";
        
        $guide .= "### Option 2: wkhtmltopdf only\n\n";
        $guide .= "```bash\n";
        $guide .= "# Ubuntu/Debian\n";
        $guide .= "sudo apt-get install -y wkhtmltopdf\n\n";
        $guide .= "# macOS\n";
        $guide .= "brew install wkhtmltopdf\n\n";
        $guide .= "# Windows\n";
        $guide .= "# Download from: https://wkhtmltopdf.org/\n";
        $guide .= "```\n\n";
        
        $guide .= "## 🚀 Usage\n\n";
        $guide .= "After installing tools:\n\n";
        $guide .= "```bash\n";
        $guide .= "# Generate both HTML and PDF\n";
        $guide .= "composer run dev-cp-pdf\n\n";
        $guide .= "# Force HTML only (faster)\n";
        $guide .= "php scripts/generate-docs-pdf.php --force-html\n";
        $guide .= "```\n\n";
        
        $guide .= "## 📁 Output\n\n";
        $guide .= "- **PDF files**: High-quality, printable documentation\n";
        $guide .= "- **HTML files**: Web-ready with navigation and search\n";
        $guide .= "- **Location**: `docs/pdf/` directory\n\n";
        
        $guide .= "## 🔧 Troubleshooting\n\n";
        $guide .= "### PDF generation fails\n";
        $guide .= "1. Ensure tools are installed: `which pandoc wkhtmltopdf`\n";
        $guide .= "2. Check permissions on output directory\n";
        $guide .= "3. Try HTML-only mode with `--force-html`\n\n";
        
        $guide .= "### HTML files look wrong\n";
        $guide .= "1. Clear browser cache\n";
        $guide .= "2. Check CSS is loading properly\n";
        $guide .= "3. Verify markdown syntax in source files\n";

        file_put_contents($this->outputDir . '/PDF-SETUP.md', $guide);
        $this->log("📖 Created installation guide: " . $this->outputDir . '/PDF-SETUP.md');
    }

    private function installPdfTools(): void
    {
        $packageManager = $this->detectPackageManager();
        
        if ($packageManager === 'unknown') {
            $this->log("⚠️  Cannot detect package manager. Please install manually.");
            return;
        }

        $packages = $this->getPackagesForManager($packageManager);
        
        foreach ($packages as $package) {
            $this->log("📦 Installing {$package}...");
            $process = new Process([$packageManager, 'install', '-y', $package]);
            $process->setTimeout(300); // 5 minutes timeout
            $process->run();
            
            if ($process->isSuccessful()) {
                $this->log("✅ Successfully installed {$package}");
            } else {
                $this->log("❌ Failed to install {$package}: " . $process->getErrorOutput());
            }
        }
    }

    private function detectPackageManager(): string
    {
        $managers = ['apt-get', 'yum', 'brew', 'pacman', 'choco'];
        
        foreach ($managers as $manager) {
            if ($this->commandExists($manager)) {
                return $manager;
            }
        }
        
        return 'unknown';
    }

    private function getPackagesForManager(string $manager): array
    {
        $packages = [
            'apt-get' => ['pandoc', 'wkhtmltopdf'],
            'yum' => ['pandoc', 'wkhtmltopdf'],
            'brew' => ['pandoc', 'wkhtmltopdf'],
            'pacman' => ['pandoc', 'wkhtmltopdf'],
            'choco' => ['pandoc', 'wkhtmltopdf']
        ];

        return $packages[$manager] ?? [];
    }

    private function commandExists(string $command): bool
    {
        $windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $testCommand = $windows ? 'where' : 'which';
        
        $process = new Process([$testCommand, $command]);
        $process->run();
        
        return $process->isSuccessful();
    }

    private function createTempCss(): string
    {
        $css = $this->getPdfCss();
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_css_');
        file_put_contents($tempFile, $css);
        return $tempFile;
    }

    private function getRelativePath(string $fullPath): string
    {
        return str_replace($this->docsDir . '/', '', $fullPath);
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function extractHeadings(string $html): array
    {
        $headings = [];
        
        // Extract h1, h2, h3, h4 headings
        if (preg_match_all('/<h([1-6])[^>]*id="([^"]*)"[^>]*>([^<]*)<\/h[1-6]>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $headings[] = [
                    'level' => (int)$match[1],
                    'anchor' => $match[2],
                    'title' => strip_tags($match[3]),
                    'page' => '' // Will be filled by PDF processor
                ];
            }
        }
        
        return $headings;
    }

    private function buildThemedHtml(string $content, array $headings, string $mdFile, PdfThemeManager $theme): string
    {
        $brand = $theme->getBrand();
        $features = $theme->getFeatures();
        $custom = $theme->getCustom();
        
        $title = basename($mdFile, '.md');
        $toc = $features['auto_toc'] ? $theme->getTableOfContentsHtml($headings) : '';
        
        // Add page breaks before major sections
        if ($features['section_breaks']) {
            $content = preg_replace('/<h2/', '<div class="page-break"></div><h2', $content);
        }
        
        // Add watermark if needed
        $watermark = '';
        if ($features['watermark'] && !empty($custom['watermark_text'])) {
            $watermark = '<div class="watermark">' . htmlspecialchars($custom['watermark_text']) . '</div>';
        }
        
        // Add draft mode if needed
        $draftBadge = '';
        if ($custom['draft_mode']) {
            $draftBadge = '<div class="draft-badge">DRAFT</div>';
        }
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - {$brand['name']}</title>
    <style>
{$theme->getCss()}
    </style>
</head>
<body>
    {$watermark}
    {$draftBadge}
    
    <!-- Table of Contents -->
    {$toc}
    
    <!-- Document Content -->
    <div class="document-content">
        {$content}
    </div>
    
    <!-- Page Breaks for Print -->
    <div class="page-break"></div>
</body>
</html>
HTML;
    }

    private function log(string $message): void
    {
        if ($this->verbose) {
            echo $message . "\n";
        }
    }

    public function showHelp(): void
    {
        echo "📚 Documentation PDF Generator\n\n";
        echo "USAGE:\n";
        echo "    php generate-docs-pdf.php [OPTIONS]\n\n";
        echo "OPTIONS:\n";
        echo "    -v, --verbose          Show detailed output\n";
        echo "    --force-html          Force HTML generation only (skip PDF tools)\n";
        echo "    --cleanup-html        Clean up intermediate HTML files after generation\n";
        echo "    --age <hours>         Only process files changed in last N hours (default: 24)\n";
        echo "    --help, -h           Show this help message\n\n";
        echo "EXAMPLES:\n";
        echo "    # Generate PDFs for recently changed files (last 24 hours)\n";
        echo "    php generate-docs-pdf.php --verbose\n\n";
        echo "    # Generate PDFs for files changed in last 6 hours\n";
        echo "    php generate-docs-pdf.php --age 6\n\n";
        echo "    # Force HTML-only generation and cleanup\n";
        echo "    php generate-docs-pdf.php --force-html --cleanup-html\n\n";
        echo "    # Full workflow with cleanup\n";
        echo "    php generate-docs-pdf.php --cleanup-html --verbose --age 12\n\n";
        echo "OUTPUT:\n";
        echo "    - HTML files: docs/pdf/*.html\n";
        echo "    - PDF files: docs/pdf/*.pdf (if tools available)\n";
        echo "    - Index: docs/pdf/index.html\n";
        echo "    - Logs: Shown with --verbose flag\n\n";
        echo "REQUIREMENTS:\n";
        echo "    - PHP 8.0+\n";
        echo "    - Optional: pandoc (for PDF generation)\n";
        echo "    - Optional: wkhtmltopdf (for PDF generation)\n";
    }
}

// Simple Process class if not available
if (!class_exists('Symfony\Component\Process\Process')) {
    class Process {
        private array $command;
        private string $output = '';
        private string $errorOutput = '';
        private int $exitCode = 0;
        private int $timeout = 60;

        public function __construct(array $command) {
            $this->command = $command;
        }

        public function setTimeout(int $timeout): void {
            $this->timeout = $timeout;
        }

        public function run(): int {
            $cmd = implode(' ', array_map('escapeshellarg', $this->command));
            $descriptorspec = [
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w'],  // stderr
            ];

            $process = proc_open($cmd, $descriptorspec, $pipes, null, null, ['timeout' => $this->timeout]);
            
            if (is_resource($process)) {
                $this->output = stream_get_contents($pipes[1]);
                $this->errorOutput = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                
                $this->exitCode = proc_close($process);
            }

            return $this->exitCode;
        }

        public function isSuccessful(): bool {
            return $this->exitCode === 0;
        }

        public function getErrorOutput(): string {
            return $this->errorOutput;
        }
    }
}

// Main execution
try {
    // Show help if requested
    if (in_array('--help', $argv) || in_array('-h', $argv)) {
        $generator = new DocumentationGenerator();
        $generator->showHelp();
        exit(0);
    }

    $generator = new DocumentationGenerator();
    $generator->setVerbose(in_array('--verbose', $argv) || in_array('-v', $argv));
    $generator->setForceHtml(in_array('--force-html', $argv));
    $generator->setCleanupHtml(in_array('--cleanup-html', $argv));
    
    // Allow custom age limit
    $ageIndex = array_search('--age', $argv);
    if ($ageIndex !== false && isset($argv[$ageIndex + 1])) {
        $hours = (int)$argv[$ageIndex + 1];
        $generator->setMaxAgeHours($hours);
        echo "⏰ Processing files changed in last {$hours} hours\n";
    }
    
    $generator->generate();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}