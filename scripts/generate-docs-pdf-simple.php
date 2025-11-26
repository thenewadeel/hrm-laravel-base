#!/usr/bin/env php
<?php

/**
 * Simple Documentation PDF Generator
 *
 * Converts markdown files in docs/ to HTML and PDF formats
 * Configuration stored in config/docs-pdf.json
 */

class SimpleDocsPdfGenerator
{
    private array $config;
    private string $rootDir;
    private string $docsDir;
    private string $outputDir;
    private bool $verbose = false;

    public function __construct()
    {
        $this->rootDir = dirname(__DIR__);
        $this->docsDir = $this->rootDir . '/docs';
        $this->loadConfig();
        $this->outputDir = $this->rootDir . '/' . $this->config['output']['directory'];
        $this->ensureDirectoryExists($this->outputDir);
    }

    private function loadConfig(): void
    {
        $configFile = $this->rootDir . '/config/docs-pdf.json';

        if (!file_exists($configFile)) {
            $this->error("Configuration file not found: {$configFile}");
            exit(1);
        }

        $config = json_decode(file_get_contents($configFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON in configuration file: " . json_last_error_msg());
            exit(1);
        }

        $this->config = $config;
    }

    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    public function generate(): void
    {
        $this->log("🚀 Starting documentation generation...");

        // Find markdown files to process
        $mdFiles = $this->findMarkdownFiles();

        if (empty($mdFiles)) {
            $this->log("ℹ️  No markdown files found in docs directory");
            return;
        }

        $this->log("📄 Found " . count($mdFiles) . " markdown files to process");

        // Check for PDF generation tools
        $canGeneratePdf = $this->checkPdfTools();

        if (!$canGeneratePdf && $this->config['output']['generate_pdf']) {
            $this->log("⚠️  PDF tools not available, generating HTML only");
            $this->config['output']['generate_pdf'] = false;
        }

        // Process each file
        $successCount = 0;
        $errorCount = 0;

        foreach ($mdFiles as $mdFile) {
            try {
                $this->convertFile($mdFile, $canGeneratePdf);
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
        $this->log("📁 Output directory: " . $this->outputDir);

        // Generate index if enabled
        if ($this->config['output']['generate_index']) {
            $this->generateIndex();
        }

        // Clean up intermediate HTML files if requested and PDFs were generated
        if ($this->config['conversion']['cleanup_html'] && $this->config['output']['generate_pdf']) {
            $this->cleanupIntermediateHtmlFiles();
        }

        // Copy generated files to public directory for static serving
        $this->copyToPublicDirectory();
    }

    private function findMarkdownFiles(): array
    {
        $mdFiles = [];
        $maxAge = $this->config['conversion']['max_age_hours'] ?? 24;
        $cutoffTime = time() - ($maxAge * 3600);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->docsDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                // Skip output directory
                if (strpos($file->getPathname(), $this->outputDir) === false) {
                    // Check file age
                    if ($file->getMTime() >= $cutoffTime) {
                        $mdFiles[] = $file->getPathname();
                    }
                }
            }
        }

        // If no recent files, process all files (first run scenario)
        if (empty($mdFiles)) {
            $this->log("ℹ️  No recently changed files found, processing all markdown files");
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'md') {
                    if (strpos($file->getPathname(), $this->outputDir) === false) {
                        $mdFiles[] = $file->getPathname();
                    }
                }
            }
        }

        return $mdFiles;
    }

    private function checkPdfTools(): bool
    {
        if ($this->config['conversion']['force_html']) {
            return false;
        }

        // Check for common PDF generation tools
        $tools = ['pandoc', 'wkhtmltopdf', 'dompdf'];

        foreach ($tools as $tool) {
            if ($this->commandExists($tool)) {
                $this->log("🔧 Found PDF tool: {$tool}");
                return true;
            }
        }

        return false;
    }

    private function convertFile(string $mdFile, bool $canGeneratePdf): void
    {
        $relativePath = $this->getRelativePath($mdFile);
        $baseName = basename($relativePath, '.md');

        // Create output path maintaining directory structure
        $outputPath = $this->outputDir . '/' . str_replace('.md', '', $relativePath);
        $outputDir = dirname($outputPath);

        if ($outputDir !== $this->outputDir) {
            $this->ensureDirectoryExists($outputDir);
        }

        // Read markdown content
        $markdown = file_get_contents($mdFile);
        $html = $this->markdownToHtml($markdown);

        // Generate HTML if enabled
        if ($this->config['output']['generate_html']) {
            $htmlPath = $outputPath . '.html';
            $this->createHtmlFile($html, $htmlPath, $baseName, $relativePath);
        }

        // Generate PDF if enabled and tools are available
        if ($this->config['output']['generate_pdf'] && $canGeneratePdf) {
            $pdfPath = $outputPath . '.pdf';
            $this->createPdfFile($html, $pdfPath, $baseName);
        }
    }

    private function markdownToHtml(string $markdown): string
    {
        $html = $markdown;

        // Code blocks with language
        $html = preg_replace('/```(\w+)?\n(.*?)\n```/s', '<pre class="code-block"><code>$2</code></pre>', $html);

        // Inline code
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);

        // Headers
        $html = preg_replace('/^# (.+)$/m', '<h1 id="$1">$1</h1>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2 id="$1">$1</h2>', $html);
        $html = preg_replace('/^### (.+)$/m', '<h3 id="$1">$1</h3>', $html);
        $html = preg_replace('/^#### (.+)$/m', '<h4 id="$1">$1</h4>', $html);

        // Bold and italic
        $html = preg_replace('/\*\*\*([^*]+)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $html);

        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);

        // Images
        if ($this->config['features']['images']) {
            $html = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" />', $html);
        }

        // Blockquotes
        $html = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $html);

        // Horizontal rules
        $html = preg_replace('/^---$/m', '<hr>', $html);

        // Line breaks and paragraphs
        $html = str_replace("\n\n", "</p>\n<p>", $html);
        $html = "<p>" . $html . "</p>";
        $html = str_replace(["\n", "<p></p>"], ["<br>", ""], $html);

        // Lists
        $html = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>\n?)/s', '<ul>$1</ul>', $html);

        return $html;
    }

    private function createHtmlFile(string $content, string $htmlPath, string $title, string $relativePath): void
    {
        $templateData = $this->prepareTemplateData($content, $title, $relativePath, false);
        $html = $this->renderTemplate('docs-html.blade.php', $templateData);
        file_put_contents($htmlPath, $html);
    }

    private function createPdfFile(string $html, string $pdfPath, string $title): void
    {
        // Prefer wkhtmltopdf for better margin control
        if ($this->commandExists('wkhtmltopdf')) {
            $this->createPdfWithWkHtml($html, $pdfPath, $title);
        } elseif ($this->commandExists('pandoc')) {
            $this->createPdfWithPandoc($html, $pdfPath, $title);
        } else {
            throw new Exception("No PDF generation tool available");
        }
    }

    private function createPdfWithPandoc(string $html, string $pdfPath, string $title): void
    {
        $tempHtml = tempnam(sys_get_temp_dir(), 'pdf_') . '.html';
        $tempCss = tempnam(sys_get_temp_dir(), 'css_') . '.css';

        file_put_contents($tempHtml, $this->wrapHtmlForPdf($html, $title));
        file_put_contents($tempCss, $this->getCss());

        $margins = $this->config['styling']['margins'];

        // Try multiple approaches for pandoc margins
        $geometry1 = "top={$margins['top']}, bottom={$margins['bottom']}, left={$margins['left']}, right={$margins['right']}";
        $geometry2 = "margin={$margins['top']} {$margins['right']} {$margins['bottom']} {$margins['left']}";
        $geometry3 = "{$margins['top']} {$margins['right']} {$margins['bottom']} {$margins['left']}";

        $this->log("🔧 Using pandoc with geometry: {$geometry1}");

        $command = [
            'pandoc',
            '-f',
            'html',
            '-t',
            'pdf',
            '--pdf-engine=wkhtmltopdf',
            '--css',
            $tempCss,
            '-V',
            "geometry:{$geometry1}",
            '-V',
            "geometry:{$geometry2}",
            '-V',
            "geometry:{$geometry3}",
            '-V',
            "margin-top={$margins['top']}",
            '-V',
            "margin-bottom={$margins['bottom']}",
            '-V',
            "margin-left={$margins['left']}",
            '-V',
            "margin-right={$margins['right']}",
            '-V',
            "fontsize={$this->config['styling']['font_size']}",
            '-V',
            "fontfamily={$this->config['styling']['font_family']}",
            '-V',
            "papersize={$this->config['styling']['page_size']}",
            '--no-tex-ligatures',
            '-o',
            $pdfPath,
            $tempHtml
        ];

        $this->executeCommand($command);

        unlink($tempHtml);
        unlink($tempCss);
    }

    private function createPdfWithWkHtml(string $html, string $pdfPath, string $title): void
    {
        $tempHtml = tempnam(sys_get_temp_dir(), 'pdf_') . '.html';
        file_put_contents($tempHtml, $this->wrapHtmlForPdf($html, $title));

        $margins = $this->config['styling']['margins'];

        // $this->log("🔧 Using wkhtmltopdf ");

        // Aggressive margin settings for wkhtmltopdf
        $command = [
            'wkhtmltopdf',
            '--page-size',
            $this->config['styling']['page_size'],
            '--margin-top',
            $margins['top'],
            '--margin-right',
            $margins['right'],
            '--margin-bottom',
            $margins['bottom'],
            '--margin-left',
            $margins['left'],
            '--encoding',
            'UTF-8',
            '--print-media-type',
            '--enable-local-file-access',
            '--disable-smart-shrinking',
            '--disable-internal-links',
            '--disable-external-links',
            '--dpi',
            '300',
            '--minimum-font-size',
            '6',
            '--page-offset',
            '0',
            '--footer-spacing',
            '0',
            '--header-spacing',
            '0',
            '--zoom',
            '1.0',
            '--background',
            '--load-error-handling',
            'ignore',
            '--load-media-error-handling',
            'ignore',
            $tempHtml,
            $pdfPath
        ];

        $this->executeCommand($command);

        unlink($tempHtml);
    }

    private function wrapHtmlForPdf(string $content, string $title): string
    {
        $templateData = $this->prepareTemplateData($content, $title, '', true);

        // Add margin variables to template data
        $margins = $this->config['styling']['margins'];
        $templateData['page_margin'] = "{$margins['top']} {$margins['right']} {$margins['bottom']} {$margins['left']}";
        $templateData['margin_top'] = $margins['top'];
        $templateData['margin_bottom'] = $margins['bottom'];
        $templateData['margin_left'] = $margins['left'];
        $templateData['margin_right'] = $margins['right'];
        $templateData['page_size'] = $this->config['styling']['page_size'];

        return $this->renderTemplate('docs-pdf.blade.php', $templateData);
    }

    private function getCss(): string
    {
        $cssFile = $this->rootDir . '/config/' . $this->config['styling']['css_file'];

        if (file_exists($cssFile)) {
            $css = file_get_contents($cssFile);
        } else {
            $css = $this->getDefaultCss();
        }

        // Add custom CSS if configured
        if (!empty($this->config['styling']['custom_css'])) {
            $css .= "\n" . $this->config['styling']['custom_css'];
        }

        // Add CSS variables from config
        $margins = $this->config['styling']['margins'];
        $marginValue = "{$margins['top']} {$margins['right']} {$margins['bottom']} {$margins['left']}";

        $css = str_replace(
            '--font-family: "Inter", system-ui, sans-serif;',
            '--font-family: ' . $this->config['styling']['font_family'] . ';',
            $css
        );
        $css = str_replace(
            '--font-size: 10pt;',
            '--font-size: ' . $this->config['styling']['font_size'] . ';',
            $css
        );
        $css = str_replace(
            '--line-height: 1.25;',
            '--line-height: ' . $this->config['styling']['line_height'] . ';',
            $css
        );
        $css = str_replace(
            'var(--page-margin, 2mm)',
            'var(--page-margin, ' . $marginValue . ')',
            $css
        );
        $css = str_replace(
            'var(--page-size, A4)',
            'var(--page-size, ' . $this->config['styling']['page_size'] . ')',
            $css
        );

        return $css;
    }

    private function getDefaultCss(): string
    {
        return 'body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6; }
                h1 { font-size: 18pt; font-weight: bold; }
                h2 { font-size: 14pt; font-weight: bold; }
                code { background-color: #f0f0f0; padding: 2px 4px; }';
    }

    private function prepareTemplateData(string $content, string $title, string $relativePath, bool $isPdf): array
    {
        $data = [
            'title' => $title,
            'brand_title' => $this->config['branding']['title'],
            'content' => $content,
            'css' => $this->getCss(),
            'nav_html' => '',
            'header_html' => '',
            'footer_html' => '',
            'watermark_html' => '',
        ];

        // Navigation
        if ($this->config['output']['navigation'] && !$isPdf) {
            $data['nav_html'] = $this->generateNavigation($relativePath);
        }

        // Header
        if ($this->config['headers']['enabled']) {
            $data['header_html'] = $this->generateHeader($title);
        }

        // Footer
        if ($this->config['footers']['enabled']) {
            $data['footer_html'] = $this->generateFooter();
        }

        // Watermark (PDF only)
        if ($isPdf && !empty($this->config['branding']['watermark'])) {
            $data['watermark_html'] = $this->generateWatermark();
        }

        return $data;
    }

    private function renderTemplate(string $templateFile, array $data): string
    {
        $templatePath = __DIR__ . '/templates/' . $templateFile;

        if (!file_exists($templatePath)) {
            throw new Exception("Template not found: {$templateFile}");
        }

        $template = file_get_contents($templatePath);

        // Replace placeholders with data
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }

    private function generateNavigation(string $currentPath): string
    {
        $navItems = [
            'README.md' => '📖 Overview',
            'SRS.md' => '📋 Requirements',
            'big picture.md' => '📊 Big Picture',
            'ERD.md' => '🗄️ Database Design',
            'project plan.md' => '📅 Project Plan',
            'timeline.md' => '⏰ Timeline',
        ];

        $navItemHtml = '';

        foreach ($navItems as $file => $title) {
            if (file_exists($this->docsDir . '/' . $file)) {
                $href = str_replace('.md', '.html', $file);
                $active = $currentPath === $file ? ' style="font-weight: bold;"' : '';
                $navItemHtml .= "<li><a href=\"{$href}\"{$active}>{$title}</a></li>";
            }
        }

        $navTemplate = file_get_contents(__DIR__ . '/templates/docs-nav.blade.php');
        return str_replace('{nav_items}', $navItemHtml, $navTemplate);
    }

    private function generateHeader(string $title): string
    {
        $headerConfig = $this->config['headers'];
        $content = str_replace(
            ['{title}', '{brand_title}'],
            [$title, $this->config['branding']['title']],
            $headerConfig['content']
        );

        $templateData = [
            'header_font_size' => $headerConfig['font_size'],
            'header_align' => $headerConfig['align'],
            'header_line_height' => $headerConfig['line_height'],
            'header_border_bottom' => $headerConfig['border_bottom'],
            'header_padding_bottom' => $headerConfig['padding_bottom'],
            'header_margin_bottom' => $headerConfig['margin_bottom'],
            'header_content' => $content,
        ];

        $template = file_get_contents(__DIR__ . '/templates/docs-header.blade.php');
        foreach ($templateData as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }

    private function generateFooter(): string
    {
        $footerConfig = $this->config['footers'];
        $content = str_replace(
            ['{page}', '{total}', '{date}'],
            ['{page}', '{total}', date('Y-m-d H:i:s')],
            $footerConfig['content']
        );

        $templateData = [
            'footer_font_size' => $footerConfig['font_size'],
            'footer_align' => $footerConfig['align'],
            'footer_line_height' => $footerConfig['line_height'],
            'footer_border_top' => $footerConfig['border_top'],
            'footer_padding_top' => $footerConfig['padding_top'],
            'footer_margin_top' => $footerConfig['margin_top'],
            'footer_content' => $content,
        ];

        $template = file_get_contents(__DIR__ . '/templates/docs-footer.blade.php');
        foreach ($templateData as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }

    private function generateWatermark(): string
    {
        $template = file_get_contents(__DIR__ . '/templates/docs-watermark.blade.php');
        return str_replace('{watermark_text}', $this->config['branding']['watermark'], $template);
    }

    private function generateIndex(): void
    {
        $indexContent = "# {$this->config['branding']['title']}\n\n";
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

        file_put_contents($this->outputDir . '/index.md', $indexContent);

        // Also generate HTML index
        if ($this->config['output']['generate_html']) {
            $html = $this->markdownToHtml($indexContent);
            $this->createHtmlFile($html, $this->outputDir . '/index.html', 'Documentation Index', 'index.md');
        }

        $this->log("📋 Generated index files");
    }

    private function copyToPublicDirectory(): void
    {
        $publicDocsDir = $this->rootDir . '/public/docs';
        $this->ensureDirectoryExists($publicDocsDir);

        $this->log("📁 Copying generated files to public directory...");

        // Copy all files from output to public
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->outputDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $copiedCount = 0;
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($this->outputDir . '/', '', $file->getPathname());
                $publicPath = $publicDocsDir . '/' . $relativePath;

                // Ensure target directory exists
                $publicDir = dirname($publicPath);
                if ($publicDir !== $publicDocsDir) {
                    $this->ensureDirectoryExists($publicDir);
                }

                if (copy($file->getPathname(), $publicPath)) {
                    $copiedCount++;
                }
            }
        }

        $this->log("✅ Copied {$copiedCount} files to public/docs/");
        $this->log("🌐 Documentation available at: /docs");
    }

    private function cleanupIntermediateHtmlFiles(): void
    {
        $this->log("🧹 Cleaning up intermediate HTML files...");

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->outputDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $cleanedCount = 0;
        $keptFiles = [];
        $removedFiles = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'html') {
                // Keep index.html and README.md
                if (!in_array($file->getBasename(), ['index.html', 'README.md'])) {
                    unlink($file->getPathname());
                    $cleanedCount++;
                    $removedFiles[] = $file->getBasename();
                } else {
                    $keptFiles[] = $file->getBasename();
                }
            }
        }

        $this->log("✅ Cleaned up {$cleanedCount} intermediate HTML files");

        if (!empty($removedFiles)) {
            $this->log("🗑️  Removed: " . implode(', ', array_slice($removedFiles, 0, 5)));
            if (count($removedFiles) > 5) {
                $this->log("    ... and " . (count($removedFiles) - 5) . " more files");
            }
        }

        if (!empty($keptFiles)) {
            $this->log("📋  Kept: " . implode(', ', $keptFiles));
        }
    }

    private function executeCommand(array $command): void
    {
        $timeout = $this->config['conversion']['timeout'] ?? 60;
        $cmd = implode(' ', array_map('escapeshellarg', $command));

        $descriptorspec = [
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes, null, null, ['timeout' => $timeout]);

        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $errorOutput = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);

            if ($exitCode !== 0) {
                throw new Exception("Command failed: " . $errorOutput);
            }
        } else {
            throw new Exception("Failed to execute command: {$cmd}");
        }
    }

    private function commandExists(string $command): bool
    {
        $windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $testCommand = $windows ? 'where' : 'which';

        $descriptorspec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open("{$testCommand} {$command}", $descriptorspec, $pipes);

        if (is_resource($process)) {
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);
            return $exitCode === 0;
        }

        return false;
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

    private function log(string $message): void
    {
        if ($this->verbose) {
            echo $message . "\n";
        }
    }

    private function error(string $message): void
    {
        echo "❌ Error: {$message}\n";
    }

    public function showHelp(): void
    {
        echo "📚 Simple Documentation PDF Generator\n\n";
        echo "USAGE:\n";
        echo "    php generate-docs-pdf-simple.php [OPTIONS]\n\n";
        echo "OPTIONS:\n";
        echo "    -v, --verbose          Show detailed output\n";
        echo "    --help, -h           Show this help message\n\n";
        echo "CONFIGURATION:\n";
        echo "    Edit config/docs-pdf.json to customize settings\n";
        echo "    Edit config/docs-pdf-theme.css for styling\n\n";
        echo "OUTPUT:\n";
        echo "    - HTML files: " . $this->config['output']['directory'] . "/*.html\n";
        echo "    - PDF files: " . $this->config['output']['directory'] . "/*.pdf (if tools available)\n";
        echo "    - Index: " . $this->config['output']['directory'] . "/index.html\n\n";
        echo "REQUIREMENTS:\n";
        echo "    - PHP 8.0+\n";
        echo "    - Optional: pandoc or wkhtmltopdf (for PDF generation)\n";
    }
}

// Main execution
try {
    global $argv;
    $argv = $argv ?? [];

    if (in_array('--help', $argv) || in_array('-h', $argv)) {
        $generator = new SimpleDocsPdfGenerator();
        $generator->showHelp();
        exit(0);
    }

    $generator = new SimpleDocsPdfGenerator();
    $generator->setVerbose(in_array('--verbose', $argv) || in_array('-v', $argv));
    $generator->generate();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
