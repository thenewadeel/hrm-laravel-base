<?php

// Laravel 12 Project Crawler - LLM Ingestible Format Generator
// Modular approach with functions for each component type

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
    'public/build', // Common Vite build output
    'public/dist' // Common Webpack output
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

// Utility function to clean content
function clean_content(string $content): string
{
    // Remove null bytes and other non-printable characters
    return preg_replace('/[\\x00-\\x1F]/', '', $content);
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

// Component extraction function
function extract_components(string $projectRoot, string $type, string $subDir, int $maxBytes, array $extensions = ['php']): array
{
    global $ignoredPaths;

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
        $skip = false;
        foreach ($ignoredPaths as $ignoredPath) {
            // Use strpos to check if the file path contains an ignored directory
            if (str_starts_with($relativePath, $ignoredPath)) {
                log_info("Skipping ignored path: " . $file->getPathname());
                // Skip the entire directory if a match is found
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
            if (!$isAllowed && $type === 'View' && str_ends_with($file->getBasename(), '.blade.php')) {
                $isAllowed = true;
            }

            // Additional check for minified or compiled files
            $fileName = $file->getBasename();
            if (str_ends_with($fileName, '.min.css') || str_ends_with($fileName, '.min.js') || str_ends_with($fileName, '.css.map') || str_ends_with($fileName, '.js.map')) {
                log_info("Skipping minified/compiled file: " . $file->getPathname());
                continue;
            }

            if ($isAllowed) {
                $content = file_get_contents($file->getPathname(), false, null, 0, $maxBytes);

                // Use a different name for routes and env files
                $name = $file->getBasename('.' . $fileExtension);
                if ($type === 'Route') {
                    $name = $file->getBasename('.php');
                } elseif ($type === 'Environment') {
                    $name = $file->getBasename();
                } elseif ($type === 'View') {
                    $name = $file->getBasename();
                }

                $components[] = [
                    'type' => $type,
                    'name' => $name,
                    'file' => str_replace('\\', '/', $file->getPathname()), // Normalize file path
                    'content' => clean_content($content)
                ];
            }
        }
    }

    return $components;
}

// Main execution function
function main(string $projectRoot, string $outputFile, bool $verbose): void
{
    log_info("Laravel 12 Project Crawler started");

    // Validate project structure
    validate_project_root($projectRoot);

    // Extract all components using the generalized function
    $components = [];
    $components = array_merge($components, extract_components($projectRoot, 'Model', 'app/Models', 5000));
    $components = array_merge($components, extract_components($projectRoot, 'Controller', 'app/Http/Controllers', 5000));
    $components = array_merge($components, extract_components($projectRoot, 'Migration', 'database/migrations', 3000));
    $components = array_merge($components, extract_components($projectRoot, 'Route', 'routes', 10000));
    $components = array_merge($components, extract_components($projectRoot, 'View', 'resources/views', 5000, ['blade.php', 'php', 'js', 'vue']));
    $components = array_merge($components, extract_components($projectRoot, 'Config', 'config', 3000));
    $components = array_merge($components, extract_components($projectRoot, 'Environment', '.', 5000, ['.env', '.env.example']));

    // Create final JSON structure
    $projectName = basename(realpath($projectRoot));
    $timestamp = (new DateTime('now', new DateTimeZone('UTC')))->format(DateTime::ATOM);

    $jsonData = [
        'project' => $projectName,
        'timestamp' => $timestamp,
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
    echo "\n=== EXTRACTION SUMMARY ===\n";
    echo "Project: $projectName\n";
    echo "Total components: $componentCount\n";
    echo "Output file: $outputFile\n";
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
