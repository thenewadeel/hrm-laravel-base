<?php

// Laravel 12 Project Crawler - Method Signatures for LLM Ingestion
// Extracts method signatures instead of full content

// Configuration
$projectRoot = $argv[1] ?? '.';
$outputFile = $argv[2] ?? 'laravel_components_llm.json';
$verbose = ($argv[3] ?? 'false') === 'true';

// Directories and patterns to ignore
$ignoredPaths = [
    'node_modules',
    'vendor',
    'storage',
    'bootstrap/cache',
    'public/build',
    'public/dist'
];

// Component-specific settings
$componentSettings = [
    'Model' => ['extract_methods' => true, 'max_methods' => 20],
    'Controller' => ['extract_methods' => true, 'max_methods' => 15],
    'Migration' => ['extract_methods' => false],
    'Route' => ['extract_methods' => false],
    'View' => ['extract_methods' => false],
    'Config' => ['extract_methods' => false],
    'Environment' => ['extract_methods' => false]
];

// Essential files to include fully
$essentialFiles = [
    'routes/web.php',
    'routes/api.php',
    'app/Http/Controllers/Controller.php',
    'app/Models/User.php',
    '.env.example'
];

// Logging functions
function log_info(string $message): void
{
    global $verbose;
    if ($verbose) {
        echo "[INFO] $message\n";
    }
}

function log_success(string $message): void
{
    echo "[SUCCESS] $message\n";
}

function log_warning(string $message): void
{
    echo "[WARNING] $message\n";
}

function log_error(string $message): void
{
    echo "[ERROR] $message\n";
}

// Simple but reliable method extraction using regex
function extract_method_signatures(string $content, int $maxMethods = 20): array
{
    $methods = [];

    // Pattern to match method signatures
    $pattern = '/(?:(public|protected|private|static|final)\s+)*function\s+(\w+)\s*\(([^)]*)\)/';

    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $modifiers = [];
        if (!empty($match[1])) {
            // Split multiple modifiers
            $modifiers = preg_split('/\s+/', trim($match[1]));
        }

        $methodName = $match[2];
        $parameters = trim($match[3]);

        // Clean up parameters
        $parameters = preg_replace('/\s+/', ' ', $parameters);

        // Get line number (approximate)
        $linesBefore = substr_count(substr($content, 0, strpos($content, $match[0])), "\n") + 1;

        $methods[] = [
            'name' => $methodName,
            'modifiers' => $modifiers,
            'parameters' => $parameters,
            'line' => $linesBefore
        ];

        if (count($methods) >= $maxMethods) {
            break;
        }
    }

    return $methods;
}

// Check if file is essential
function is_essential_file(string $filePath): bool
{
    global $essentialFiles, $projectRoot;
    $relativePath = str_replace('\\', '/', substr($filePath, strlen($projectRoot) + 1));
    return in_array($relativePath, $essentialFiles);
}

// Validation function
function validate_project_root(string $projectRoot): void
{
    if (!is_dir($projectRoot)) {
        log_error("Project root directory '$projectRoot' does not exist");
        exit(1);
    }

    if (!is_file("$projectRoot/artisan")) {
        log_error("Directory '$projectRoot' does not appear to be a Laravel project (artisan not found)");
        exit(1);
    }

    log_info("Validated Laravel project at: $projectRoot");
}

// Extract class name from PHP file using simple regex
function extract_class_name(string $content): string
{
    if (preg_match('/class\s+(\w+)/', $content, $matches)) {
        return $matches[1];
    }
    return '';
}

// Extract namespace from PHP file using simple regex
function extract_namespace(string $content): string
{
    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

// Component extraction function
function extract_components(string $projectRoot, string $type, string $subDir, array $extensions = ['php']): array
{
    global $ignoredPaths, $componentSettings;

    $dirPath = "$projectRoot/$subDir";
    $components = [];

    if (!is_dir($dirPath)) {
        log_warning("$type directory not found: $dirPath");
        return $components;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        $relativePath = substr($file->getPathname(), strlen($projectRoot) + 1);

        // Skip ignored paths
        $skip = false;
        foreach ($ignoredPaths as $ignoredPath) {
            if (str_starts_with($relativePath, $ignoredPath)) {
                log_info("Skipping ignored path: " . $file->getPathname());
                if ($file->isDir()) {
                    $files->next();
                }
                $skip = true;
                break;
            }
        }
        if ($skip) {
            continue;
        }

        if ($file->isFile()) {
            $fileExtension = $file->getExtension();
            $isAllowed = in_array($fileExtension, $extensions);

            // Special handling for blade templates
            if (!$isAllowed && $type === 'View' && str_ends_with($file->getBasename(), '.blade.php')) {
                $isAllowed = true;
            }

            // Skip minified/compiled files
            $fileName = $file->getBasename();
            if (
                str_ends_with($fileName, '.min.css') || str_ends_with($fileName, '.min.js') ||
                str_ends_with($fileName, '.css.map') || str_ends_with($fileName, '.js.map')
            ) {
                log_info("Skipping minified/compiled file: " . $file->getPathname());
                continue;
            }

            if ($isAllowed) {
                $isEssential = is_essential_file($file->getPathname());
                $content = file_get_contents($file->getPathname());

                // Use appropriate naming
                $name = $file->getBasename('.' . $fileExtension);
                if ($type === 'Route') {
                    $name = $file->getBasename('.php');
                } elseif ($type === 'Environment') {
                    $name = $file->getBasename();
                } elseif ($type === 'View') {
                    $name = $file->getBasename();
                }

                $componentData = [
                    'type' => $type,
                    'name' => $name,
                    'file' => str_replace('\\', '/', $relativePath),
                    'essential' => $isEssential
                ];

                // Extract method signatures for appropriate components
                if ($componentSettings[$type]['extract_methods'] && $fileExtension === 'php') {
                    $className = extract_class_name($content);
                    $namespace = extract_namespace($content);

                    if (!empty($className)) {
                        $componentData['class'] = $className;
                        if (!empty($namespace)) {
                            $componentData['namespace'] = $namespace;
                        }

                        $maxMethods = $componentSettings[$type]['max_methods'] ?? 20;
                        $methods = extract_method_signatures($content, $maxMethods);
                        $componentData['methods'] = $methods;
                        $componentData['method_count'] = count($methods);

                        log_info("Found " . count($methods) . " methods in " . $className);
                    }
                }

                // For essential files, include a small snippet of content
                if ($isEssential && strlen($content) > 500) {
                    $componentData['content_preview'] = substr($content, 0, 500) . '...';
                } elseif ($isEssential) {
                    $componentData['content'] = $content;
                }

                // For routes, include a summary of route definitions
                if ($type === 'Route') {
                    preg_match_all('/Route::\w+\([^;]*\);/', $content, $matches);
                    if (!empty($matches[0])) {
                        $routeSummary = [];
                        foreach ($matches[0] as $routeDef) {
                            // Simplify route definition for summary
                            $simplified = preg_replace('/\s+/', ' ', $routeDef);
                            if (strlen($simplified) > 100) {
                                $simplified = substr($simplified, 0, 100) . '...';
                            }
                            $routeSummary[] = $simplified;
                        }
                        $componentData['route_summary'] = $routeSummary;
                    }
                }

                $components[] = $componentData;
            }
        }
    }

    return $components;
}

// Extract only key information from composer.json
function extract_composer_info(string $projectRoot): array
{
    $composerFile = "$projectRoot/composer.json";
    if (!file_exists($composerFile)) {
        return [];
    }

    $composerData = json_decode(file_get_contents($composerFile), true);
    if (!$composerData) {
        return [];
    }

    return [
        'name' => $composerData['name'] ?? 'Unknown',
        'description' => $composerData['description'] ?? '',
        'require' => array_keys($composerData['require'] ?? []),
        'require-dev' => array_keys($composerData['require-dev'] ?? [])
    ];
}

// Main execution function
function main(string $projectRoot, string $outputFile, bool $verbose): void
{
    log_info("Laravel 12 Project Crawler started (Method Signatures for LLM)");

    // Validate project structure
    validate_project_root($projectRoot);

    // Extract all components using the generalized function
    $components = [];
    $components = array_merge($components, extract_components($projectRoot, 'Model', 'app/Models'));
    $components = array_merge($components, extract_components($projectRoot, 'Controller', 'app/Http/Controllers'));
    $components = array_merge($components, extract_components($projectRoot, 'Migration', 'database/migrations'));
    $components = array_merge($components, extract_components($projectRoot, 'Route', 'routes'));
    $components = array_merge($components, extract_components($projectRoot, 'View', 'resources/views', ['blade.php', 'php']));
    $components = array_merge($components, extract_components($projectRoot, 'Config', 'config'));
    $components = array_merge($components, extract_components($projectRoot, 'Environment', '.', ['.env', '.env.example']));

    // Extract composer information
    $composerInfo = extract_composer_info($projectRoot);

    // Create final JSON structure
    $projectName = basename(realpath($projectRoot));
    $timestamp = (new DateTime('now', new DateTimeZone('UTC')))->format(DateTime::ATOM);

    $jsonData = [
        'project' => $projectName,
        'composer' => $composerInfo,
        'timestamp' => $timestamp,
        'component_count' => count($components),
        'components' => $components
    ];

    // Save output with a single, proper JSON encode
    $jsonOutput = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($jsonOutput === false) {
        log_error("Failed to encode JSON data: " . json_last_error_msg());
        exit(1);
    }

    file_put_contents($outputFile, $jsonOutput);
    log_success("Output saved to: $outputFile");

    // Show summary
    show_summary($projectName, count($components), $outputFile);

    log_success("Extraction completed successfully!");
}

function show_summary(string $projectName, int $componentCount, string $outputFile): void
{
    $fileSize = filesize($outputFile);
    echo "\n=== EXTRACTION SUMMARY ===\n";
    echo "Project: $projectName\n";
    echo "Total components: $componentCount\n";
    echo "Output file: $outputFile\n";
    echo "File size: " . round($fileSize / 1024, 2) . " KB\n";
    echo "==========================\n";
}

// Check for help flag
if (in_array('-h', $argv) || in_array('--help', $argv)) {
    echo "Usage: php $argv[0] [PROJECT_ROOT] [OUTPUT_FILE] [VERBOSE]\n\n";
    echo "PROJECT_ROOT  : Path to Laravel project (default: current directory)\n";
    echo "OUTPUT_FILE   : Output JSON file (default: laravel_components_llm.json)\n";
    echo "VERBOSE       : Show verbose output (true/false, default: false)\n\n";
    echo "Example: php $argv[0] /path/to/laravel-project project_analysis.json true\n";
    exit(0);
}

// Run main function
main($projectRoot, $outputFile, $verbose);
