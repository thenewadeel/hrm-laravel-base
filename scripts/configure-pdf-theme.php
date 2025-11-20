#!/usr/bin/env php
<?php

/**
 * PDF Theme Configuration CLI
 * 
 * Interactive command to configure PDF theme settings
 */

require_once __DIR__ . '/../app/Services/PdfThemeManager.php';

class ThemeConfigurator
{
    private PdfThemeManager $theme;
    private array $config;

    public function __construct()
    {
        $this->theme = new PdfThemeManager();
        $this->config = $this->theme->getConfig();
    }

    public function run(): void
    {
        echo "🎨 PDF Theme Configuration\n\n";
        echo "Current theme: " . $this->config['brand']['name'] . "\n\n";
        
        while (true) {
            $this->showMenu();
            $choice = $this->getChoice();
            
            if ($choice === 'exit') {
                break;
            }
            
            $this->handleChoice($choice);
        }
        
        echo "\n✅ Theme configuration saved!\n";
    }

    private function showMenu(): void
    {
        echo "📋 Configuration Options:\n\n";
        echo "1. 🏢 Brand Information (name, company, logo)\n";
        echo "2. 🎨 Theme Colors (primary, secondary, accent)\n";
        echo "3. 📐 Layout Settings (margins, fonts, sizes)\n";
        echo "4. ⚙️ Features (TOC, page numbers, watermarks)\n";
        echo "5. 🎯 Custom Options (draft mode, confidential text)\n";
        echo "6. 📋 Show Current Configuration\n";
        echo "7. 🔄 Reset to Defaults\n";
        echo "8. 💾 Save and Exit\n";
        echo "0. ❌ Exit without Saving\n\n";
        echo "Choose option (0-8): ";
    }

    private function getChoice(): string
    {
        $handle = fopen('php://stdin', 'r');
        $choice = trim(fgets($handle));
        fclose($handle);
        
        return $choice;
    }

    private function handleChoice(string $choice): void
    {
        switch ($choice) {
            case '1':
                $this->configureBrand();
                break;
            case '2':
                $this->configureColors();
                break;
            case '3':
                $this->configureLayout();
                break;
            case '4':
                $this->configureFeatures();
                break;
            case '5':
                $this->configureCustom();
                break;
            case '6':
                $this->showConfiguration();
                break;
            case '7':
                $this->resetToDefaults();
                break;
            case '8':
                $this->saveAndExit();
                break;
            case '0':
                echo "\n👋 Exiting without saving...\n";
                exit(0);
            default:
                echo "\n❌ Invalid choice. Please try again.\n\n";
                break;
        }
    }

    private function configureBrand(): void
    {
        echo "\n🏢 Brand Configuration\n\n";
        
        $this->config['brand']['name'] = $this->prompt('Brand Name', $this->config['brand']['name']);
        $this->config['brand']['tagline'] = $this->prompt('Tagline', $this->config['brand']['tagline']);
        $this->config['brand']['company'] = $this->prompt('Company Name', $this->config['brand']['company']);
        $this->config['brand']['website'] = $this->prompt('Website', $this->config['brand']['website']);
        $this->config['brand']['logo'] = $this->prompt('Logo (emoji/text)', $this->config['brand']['logo']);
        $this->config['brand']['version'] = $this->prompt('Version', $this->config['brand']['version']);
        $this->config['brand']['copyright'] = $this->prompt('Copyright', $this->config['brand']['copyright']);
    }

    private function configureColors(): void
    {
        echo "\n🎨 Theme Colors\n\n";
        
        $this->config['theme']['primary_color'] = $this->prompt('Primary Color', $this->config['theme']['primary_color']);
        $this->config['theme']['secondary_color'] = $this->prompt('Secondary Color', $this->config['theme']['secondary_color']);
        $this->config['theme']['accent_color'] = $this->prompt('Accent Color', $this->config['theme']['accent_color']);
        $this->config['theme']['success_color'] = $this->prompt('Success Color', $this->config['theme']['success_color']);
        $this->config['theme']['warning_color'] = $this->prompt('Warning Color', $this->config['theme']['warning_color']);
        $this->config['theme']['error_color'] = $this->prompt('Error Color', $this->config['theme']['error_color']);
        $this->config['theme']['background_color'] = $this->prompt('Background Color', $this->config['theme']['background_color']);
        $this->config['theme']['text_color'] = $this->prompt('Text Color', $this->config['theme']['text_color']);
        $this->config['theme']['border_color'] = $this->prompt('Border Color', $this->config['theme']['border_color']);
    }

    private function configureLayout(): void
    {
        echo "\n📐 Layout Settings\n\n";
        
        $this->config['layout']['page_size'] = $this->prompt('Page Size', $this->config['layout']['page_size']);
        $this->config['layout']['margin_top'] = $this->prompt('Top Margin (mm)', $this->config['layout']['margin_top']);
        $this->config['layout']['margin_bottom'] = $this->prompt('Bottom Margin (mm)', $this->config['layout']['margin_bottom']);
        $this->config['layout']['margin_left'] = $this->prompt('Left Margin (mm)', $this->config['layout']['margin_left']);
        $this->config['layout']['margin_right'] = $this->prompt('Right Margin (mm)', $this->config['layout']['margin_right']);
        $this->config['layout']['font_size'] = $this->prompt('Font Size (pt)', $this->config['layout']['font_size']);
        $this->config['layout']['line_height'] = $this->prompt('Line Height', $this->config['layout']['line_height']);
        $this->config['layout']['header_font'] = $this->prompt('Header Font', $this->config['layout']['header_font']);
        $this->config['layout']['body_font'] = $this->prompt('Body Font', $this->config['layout']['body_font']);
        $this->config['layout']['code_font'] = $this->prompt('Code Font', $this->config['layout']['code_font']);
    }

    private function configureFeatures(): void
    {
        echo "\n⚙️ Features Configuration\n\n";
        
        $this->config['features']['auto_toc'] = $this->confirm('Auto Table of Contents', $this->config['features']['auto_toc']);
        $this->config['features']['page_numbers'] = $this->confirm('Page Numbers', $this->config['features']['page_numbers']);
        $this->config['features']['section_breaks'] = $this->confirm('Section Breaks', $this->config['features']['section_breaks']);
        $this->config['features']['watermark'] = $this->confirm('Watermark', $this->config['features']['watermark']);
        $this->config['features']['bookmarks'] = $this->confirm('Bookmarks', $this->config['features']['bookmarks']);
        $this->config['features']['links'] = $this->confirm('Clickable Links', $this->config['features']['links']);
    }

    private function configureCustom(): void
    {
        echo "\n🎯 Custom Options\n\n";
        
        $this->config['custom']['watermark_text'] = $this->prompt('Watermark Text', $this->config['custom']['watermark_text']);
        $this->config['custom']['confidential_text'] = $this->prompt('Confidential Text', $this->config['custom']['confidential_text']);
        $this->config['custom']['draft_mode'] = $this->confirm('Draft Mode', $this->config['custom']['draft_mode']);
        $this->config['custom']['print_date'] = $this->confirm('Print Date', $this->config['custom']['print_date']);
        $this->config['custom']['author_info'] = $this->confirm('Author Info', $this->config['custom']['author_info']);
    }

    private function showConfiguration(): void
    {
        echo "\n📋 Current Configuration:\n\n";
        echo json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    }

    private function resetToDefaults(): void
    {
        echo "\n🔄 Resetting to defaults...\n";
        
        // Create new theme manager with defaults
        $defaultTheme = new PdfThemeManager();
        $this->config = $defaultTheme->getConfig();
        
        echo "✅ Reset to default configuration.\n\n";
    }

    private function saveAndExit(): void
    {
        echo "\n💾 Saving configuration...\n";
        
        // Save using reflection to access private properties
        $reflection = new ReflectionClass($this->theme);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($this->theme, $this->config);
        
        echo "✅ Configuration saved successfully!\n";
        exit(0);
    }

    private function prompt(string $prompt, string $default): string
    {
        echo "{$prompt} [{$default}]: ";
        $handle = fopen('php://stdin', 'r');
        $input = trim(fgets($handle));
        fclose($handle);
        
        return $input ?: $default;
    }

    private function confirm(string $prompt, bool $default): bool
    {
        $defaultStr = $default ? 'Y/n' : 'y/N';
        echo "{$prompt} [{$defaultStr}]: ";
        $handle = fopen('php://stdin', 'r');
        $input = strtolower(trim(fgets($handle)));
        fclose($handle);
        
        if (empty($input)) {
            return $default;
        }
        
        return in_array($input[0], ['y', 'yes', 'true', '1']);
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