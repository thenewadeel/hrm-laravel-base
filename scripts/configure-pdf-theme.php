#!/usr/bin/env php
<?php

/**
 * PDF Theme Configuration CLI
 * 
 * Interactive command to configure PDF theme settings
 */

class ThemeConfigurator
{
    private string $configPath;
    private array $config;

    public function __construct()
    {
        $this->configPath = __DIR__ . '/../config/docs-pdf.json';
        $this->loadConfig();
    }

    public function run(): void
    {
        echo "🎨 PDF Theme Configuration\n\n";
        echo "Current title: " . $this->config['branding']['title'] . "\n\n";
        
        while (true) {
            $this->showMenu();
            $choice = $this->getChoice();
            
            switch ($choice) {
                case 1:
                    $this->configureBranding();
                    break;
                case 2:
                    $this->configureHeaders();
                    break;
                case 3:
                    $this->configureFooters();
                    break;
                case 4:
                    $this->configureStyling();
                    break;
                case 5:
                    $this->showCurrentConfig();
                    break;
                case 0:
                    echo "👋 Goodbye!\n";
                    exit(0);
                default:
                    echo "❌ Invalid choice. Please try again.\n\n";
            }
        }
    }

    private function showMenu(): void
    {
        echo "What would you like to configure?\n\n";
        echo "1. 🏷️  Branding (title, watermark)\n";
        echo "2. 📄 Headers (content, styling)\n";
        echo "3. 📋 Footers (content, page numbers)\n";
        echo "4. 🎨 Styling (fonts, margins, colors)\n";
        echo "5. 📖 Show current configuration\n";
        echo "0. 🚪 Exit\n\n";
        echo "Choice: ";
    }

    private function getChoice(): int
    {
        $handle = fopen('php://stdin', 'r');
        $choice = trim(fgets($handle));
        fclose($handle);
        return (int)$choice;
    }

    private function configureBranding(): void
    {
        echo "\n🏷️  Branding Configuration\n";
        echo "Current title: " . $this->config['branding']['title'] . "\n";
        echo "Enter new title (or press Enter to keep current): ";
        
        $title = $this->getInput();
        if (!empty($title)) {
            $this->config['branding']['title'] = $title;
        }

        echo "Current watermark: '" . $this->config['branding']['watermark'] . "'\n";
        echo "Enter new watermark (or press Enter to keep current): ";
        
        $watermark = $this->getInput();
        if (!empty($watermark)) {
            $this->config['branding']['watermark'] = $watermark;
        }

        $this->saveConfig();
        echo "✅ Branding updated!\n\n";
    }

    private function configureHeaders(): void
    {
        echo "\n📄 Header Configuration\n";
        echo "Current enabled: " . ($this->config['headers']['enabled'] ? 'Yes' : 'No') . "\n";
        echo "Enable headers? (y/n, or press Enter to keep current): ";
        
        $input = strtolower($this->getInput());
        if ($input === 'y' || $input === 'yes') {
            $this->config['headers']['enabled'] = true;
        } elseif ($input === 'n' || $input === 'no') {
            $this->config['headers']['enabled'] = false;
        }

        echo "Current content: " . $this->config['headers']['content'] . "\n";
        echo "Enter new header content (or press Enter to keep current): ";
        
        $content = $this->getInput();
        if (!empty($content)) {
            $this->config['headers']['content'] = $content;
        }

        $this->saveConfig();
        echo "✅ Headers updated!\n\n";
    }

    private function configureFooters(): void
    {
        echo "\n📋 Footer Configuration\n";
        echo "Current enabled: " . ($this->config['footers']['enabled'] ? 'Yes' : 'No') . "\n";
        echo "Enable footers? (y/n, or press Enter to keep current): ";
        
        $input = strtolower($this->getInput());
        if ($input === 'y' || $input === 'yes') {
            $this->config['footers']['enabled'] = true;
        } elseif ($input === 'n' || $input === 'no') {
            $this->config['footers']['enabled'] = false;
        }

        echo "Current content: " . $this->config['footers']['content'] . "\n";
        echo "Enter new footer content (or press Enter to keep current): ";
        
        $content = $this->getInput();
        if (!empty($content)) {
            $this->config['footers']['content'] = $content;
        }

        $this->saveConfig();
        echo "✅ Footers updated!\n\n";
    }

    private function configureStyling(): void
    {
        echo "\n🎨 Styling Configuration\n";
        echo "Current font size: " . $this->config['styling']['font_size'] . "\n";
        echo "Enter new font size (or press Enter to keep current): ";
        
        $fontSize = $this->getInput();
        if (!empty($fontSize)) {
            $this->config['styling']['font_size'] = $fontSize;
        }

        echo "Current font family: " . $this->config['styling']['font_family'] . "\n";
        echo "Enter new font family (or press Enter to keep current): ";
        
        $fontFamily = $this->getInput();
        if (!empty($fontFamily)) {
            $this->config['styling']['font_family'] = $fontFamily;
        }

        $this->saveConfig();
        echo "✅ Styling updated!\n\n";
    }

    private function showCurrentConfig(): void
    {
        echo "\n📖 Current Configuration\n";
        echo "==================\n\n";
        
        echo "🏷️  Branding:\n";
        echo "  Title: " . $this->config['branding']['title'] . "\n";
        echo "  Watermark: '" . $this->config['branding']['watermark'] . "'\n\n";
        
        echo "📄 Headers:\n";
        echo "  Enabled: " . ($this->config['headers']['enabled'] ? 'Yes' : 'No') . "\n";
        echo "  Content: " . $this->config['headers']['content'] . "\n";
        echo "  Font Size: " . $this->config['headers']['font_size'] . "\n\n";
        
        echo "📋 Footers:\n";
        echo "  Enabled: " . ($this->config['footers']['enabled'] ? 'Yes' : 'No') . "\n";
        echo "  Content: " . $this->config['footers']['content'] . "\n";
        echo "  Font Size: " . $this->config['footers']['font_size'] . "\n\n";
        
        echo "🎨 Styling:\n";
        echo "  Font Family: " . $this->config['styling']['font_family'] . "\n";
        echo "  Font Size: " . $this->config['styling']['font_size'] . "\n";
        echo "  Page Size: " . $this->config['styling']['page_size'] . "\n\n";
    }

    private function getInput(): string
    {
        $handle = fopen('php://stdin', 'r');
        $input = trim(fgets($handle));
        fclose($handle);
        return $input;
    }

    private function loadConfig(): void
    {
        if (!file_exists($this->configPath)) {
            echo "❌ Configuration file not found: {$this->configPath}\n";
            exit(1);
        }

        $json = file_get_contents($this->configPath);
        $this->config = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "❌ Invalid JSON in configuration file: " . json_last_error_msg() . "\n";
            exit(1);
        }
    }

    private function saveConfig(): void
    {
        $json = json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if (file_put_contents($this->configPath, $json) === false) {
            echo "❌ Failed to save configuration file\n";
            return;
        }
    }
}

// Main execution
try {
    $configurator = new ThemeConfigurator();
    $configurator->run();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}