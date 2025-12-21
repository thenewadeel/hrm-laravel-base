#!/usr/bin/env php
<?php

/**
 * Transparent Dusk Test Runner with Real-Time Progress
 * 
 * This script provides real-time visibility into test execution:
 * 1. Live test progress indicators
 * 2. Detailed batch execution tracking
 * 3. Clear success/failure reporting
 * 4. Interactive progress display
 */

$baseDir = dirname(__DIR__);
$configFile = $baseDir . '/config/dusk-testing.json';

// Load configuration
if (!file_exists($configFile)) {
    echo "❌ Configuration file not found: {$configFile}\n";
    exit(1);
}

$config = json_decode(file_get_contents($configFile), true);
if (!$config) {
    echo "❌ Invalid JSON in configuration file: {$configFile}\n";
    exit(1);
}

// Command line options
$options = getopt('', [
    'category:',
    'list-categories',
    'show-categorization',
    'verbose',
    'filter:',
    'dry-run',
    'help'
]);

if (isset($options['help'])) {
    echo "Transparent Dusk Test Runner\n";
    echo "============================\n\n";
    echo "Usage: php scripts/dusk-runner.php [options]\n\n";
    echo "Options:\n";
    echo "  --category <name>     Run only tests from specified category\n";
    echo "  --list-categories     List all available categories\n";
    echo "  --show-categorization Show how tests are categorized\n";
    echo "  --filter <pattern>    Filter tests by filename pattern\n";
    echo "  --dry-run             Show what would be executed without running\n";
    echo "  --verbose             Show failed test details\n";
    echo "  --help                This help message\n\n";
    echo "Features:\n";
    echo "  ✅ Real-time test progress with live indicators\n";
    echo "  ✅ Detailed batch execution tracking\n";
    echo "  ✅ Clear success/failure reporting\n";
    echo "  ✅ Interactive progress display\n\n";
    echo "Examples:\n";
    echo "  php scripts/dusk-runner.php --category basic\n";
    echo "  php scripts/dusk-runner.php --filter Dashboard\n";
    echo "  php scripts/dusk-runner.php --verbose\n";
    exit(0);
}

$testsDir = $baseDir . '/tests/Browser';
$categoryRules = $config['categorization']['rules'];
$executionConfig = $config['execution'];
$loggingConfig = $config['logging'];

// Initialize
$allTests = [];

echo "🚀 Transparent Dusk Test Runner\n";
echo "================================\n";

// Function to categorize a test
function categorizeTest(string $testPath, array $rules): array
{
    $fileName = basename($testPath, '.php');
    $relativePath = str_replace(dirname(dirname($testPath)) . '/tests/Browser/', '', $testPath);
    
    // Read limited file content for content-based categorization
    $content = '';
    if (file_exists($testPath)) {
        $fileContent = file_get_contents($testPath);
        $content = strtolower(substr($fileContent, 0, 2000));
    }
    
    foreach ($rules as $rule) {
        // Check filename patterns
        foreach ($rule['patterns'] as $pattern) {
            if (fnmatch($pattern, $fileName) || fnmatch($pattern, $relativePath)) {
                return [
                    'category' => $rule['name'],
                    'rule' => $rule,
                    'match_type' => 'filename',
                    'pattern' => $pattern
                ];
            }
        }
        
        // Check content patterns
        foreach ($rule['contentPatterns'] as $pattern) {
            $searchTerm = str_replace('*', '', strtolower($pattern));
            if (!empty($searchTerm) && strpos($content, $searchTerm) !== false) {
                return [
                    'category' => $rule['name'],
                    'rule' => $rule,
                    'match_type' => 'content',
                    'pattern' => $pattern
                ];
            }
        }
    }
    
    // Default to simple category
    $defaultRule = null;
    foreach ($rules as $rule) {
        if ($rule['name'] === 'simple') {
            $defaultRule = $rule;
            break;
        }
    }
    if (!$defaultRule) {
        $defaultRule = $rules[0];
    }
    
    return [
        'category' => $defaultRule['name'],
        'rule' => $defaultRule,
        'match_type' => 'default',
        'pattern' => 'none'
    ];
}

// Discover all tests
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($testsDir, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $testFile = $file->getPathname();
        $fileName = $file->getBasename('.php');
        
        // Skip non-test files
        if (str_contains($fileName, 'TestCase') || 
            str_contains($fileName, 'Concern') || 
            str_contains($fileName, 'Trait') ||
            str_contains($fileName, 'Assertions') ||
            str_contains($fileName, 'Fixtures') ||
            str_contains($fileName, 'Utilities') ||
            str_starts_with($fileName, '.')) {
            continue;
        }
        
        $categorization = categorizeTest($testFile, $categoryRules);
        $categoryName = $categorization['category'];
        
        $allTests[] = [
            'file' => $testFile,
            'name' => $fileName,
            'relative_path' => str_replace($baseDir . '/', '', $testFile),
            'category' => $categoryName,
            'rule' => $categorization['rule'],
            'match_type' => $categorization['match_type'],
            'pattern' => $categorization['pattern']
        ];
    }
}

// Apply filter if specified
if (!empty($options['filter'] ?? '')) {
    $filter = $options['filter'];
    $allTests = array_filter($allTests, fn($test) => 
        str_contains($test['name'], $filter) || 
        str_contains($test['relative_path'], $filter)
    );
}

// Group tests by category
$testsByCategory = [];
foreach ($allTests as $test) {
    $testsByCategory[$test['category']][] = $test;
}

// Handle list-categories option
if (isset($options['list-categories'])) {
    echo "Available test categories:\n";
    foreach ($categoryRules as $rule) {
        $testCount = isset($testsByCategory[$rule['name']]) ? count($testsByCategory[$rule['name']]) : 0;
        echo "  {$rule['name']}: {$rule['description']} ({$rule['timeout']}s timeout, {$testCount} tests)\n";
    }
    exit(0);
}

// Handle show-categorization option
if (isset($options['show-categorization'])) {
    echo "Test Categorization:\n";
    echo "=====================\n\n";
    
    foreach ($testsByCategory as $category => $tests) {
        $rule = $categoryRules[array_search($category, array_column($categoryRules, 'name'))];
        echo "📂 {$category} ({$rule['description']})\n";
        echo "   Timeout: {$rule['timeout']}s | Max per batch: {$rule['maxTestsPerBatch']}\n";
        echo "   Tests: " . count($tests) . "\n";
        
        if (isset($options['verbose'])) {
            foreach ($tests as $test) {
                echo "     - {$test['name']} ({$test['match_type']}: {$test['pattern']})\n";
            }
        }
        echo "\n";
    }
    exit(0);
}

// Filter by category if specified
$targetCategory = $options['category'] ?? null;
if ($targetCategory) {
    $testsByCategory = array_filter($testsByCategory, fn($cat) => $cat === $targetCategory, ARRAY_FILTER_USE_KEY);
}

// Display summary
$totalTests = array_sum(array_map('count', $testsByCategory));
echo "📊 Discovery Complete\n";
echo "Test Files Found: {$totalTests}\n";
echo "Categories: " . count($testsByCategory) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

foreach ($testsByCategory as $category => $tests) {
    $rule = $categoryRules[array_search($category, array_column($categoryRules, 'name'))];
    echo "📁 {$category}: " . str_pad(count($tests), 3, " ", STR_PAD_LEFT) . " tests ({$rule['timeout']}s timeout)\n";
}

if ($totalTests === 0) {
    echo "❌ No tests found matching criteria.\n";
    exit(1);
}

// Dry run mode
if (isset($options['dry-run'])) {
    echo "\n🔍 Dry run mode - Execution plan:\n";
    echo "=================================\n";
    
    foreach ($testsByCategory as $category => $tests) {
        $rule = $categoryRules[array_search($category, array_column($categoryRules, 'name'))];
        $batches = array_chunk($tests, $rule['maxTestsPerBatch']);
        
        echo "\n📂 {$category} ({$rule['timeout']}s timeout per batch)\n";
        echo "   " . count($batches) . " batches of max {$rule['maxTestsPerBatch']} tests each:\n";
        
        foreach ($batches as $i => $batch) {
            echo "     Batch " . ($i + 1) . ": " . implode(', ', array_column($batch, 'name')) . "\n";
        }
    }
    exit(0);
}

// Prepare output directory
$outputDir = $baseDir . '/' . $loggingConfig['outputDirectory'];
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Execute tests with transparent progress
echo "\n🏁 Starting Test Execution\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$startTime = microtime(true);
$results = [
    'total' => 0,
    'passed' => 0,
    'failed' => 0,
    'batches' => [],
    'categories' => []
];

$overallStart = microtime(true);
$totalCategories = count($testsByCategory);
$currentCategory = 0;

foreach ($testsByCategory as $category => $tests) {
    $currentCategory++;
    $rule = $categoryRules[array_search($category, array_column($categoryRules, 'name'))];
    $batches = array_chunk($tests, $rule['maxTestsPerBatch']);
    
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║ 📂 Category {$currentCategory}/{$totalCategories}: " . str_pad($category, 20, " ", STR_PAD_RIGHT) . " ║\n";
    echo "║    " . str_pad($rule['description'], 55, " ", STR_PAD_RIGHT) . " ║\n";
    echo "║    Tests: " . str_pad(count($tests), 3, " ", STR_PAD_LEFT) . " | Batches: " . str_pad(count($batches), 2, " ", STR_PAD_LEFT) . " | Timeout: {$rule['timeout']}s ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    
    $categoryResults = [
        'total' => 0,
        'passed' => 0,
        'failed' => 0,
        'batches' => []
    ];
    
    foreach ($batches as $batchIndex => $batch) {
        $batchStart = microtime(true);
        $testNames = array_column($batch, 'name');
        $filterPattern = implode('|', $testNames);
        
        echo "\n  🔄 Batch " . ($batchIndex + 1) . "/" . count($batches) . " | Tests: " . count($batch) . "\n";
        echo "  " . str_repeat("─", 60) . "\n";
        echo "  📋 Running: " . implode(', ', array_slice($testNames, 0, 3));
        if (count($testNames) > 3) {
            echo " and " . (count($testNames) - 3) . " more...";
        }
        echo "\n";
        
        // Prepare batch output file
        $batchOutputFile = str_replace('{category}', $category, $loggingConfig['batchOutputFormat']);
        $batchOutputFile = $baseDir . '/' . $batchOutputFile;
        
        // Run with live progress
        $command = "cd {$baseDir} && timeout {$rule['timeout']}s php artisan dusk --filter=\"{$filterPattern}\" 2>&1";
        
        $process = popen($command, 'r');
        $liveOutput = '';
        $testResults = [];
        $currentTest = '';
        
        echo "  📊 Progress: ";
        
        while (!feof($process)) {
            $line = fgets($process);
            if ($line) {
                $liveOutput .= $line;
                $line = trim($line);
                
                // Live progress indicator
                if (str_contains($line, '✅') || str_contains($line, '⨯') || str_contains($line, '✓') || str_contains($line, '✗')) {
                    echo "⚡";
                    flush();
                    
                    // Extract test name and result
                    if (preg_match('/^(.*?)\s+(✅|⨯|✓|✗)/', $line, $matches)) {
                        $testName = trim($matches[1]);
                        $result = str_contains($matches[2], '✅') || str_contains($matches[2], '✓') ? 'PASS' : 'FAIL';
                        $testResults[] = ['name' => $testName, 'result' => $result];
                    }
                }
                
                // Show timeout or error
                if (str_contains($line, 'TimeoutException') || str_contains($line, 'Error')) {
                    echo "❌";
                }
            }
        }
        
        $returnCode = pclose($process);
        $batchRuntime = round(microtime(true) - $batchStart, 2);
        
        // Parse results
        $batchPassed = 0;
        $batchFailed = 0;
        
        foreach ($testResults as $result) {
            if ($result['result'] === 'PASS') {
                $batchPassed++;
            } else {
                $batchFailed++;
            }
        }
        
        // Fallback parsing
        if ($batchPassed === 0 && $batchFailed === 0) {
            preg_match_all('/^\s*(✅|⨯|✓|✗)/m', $liveOutput, $matches);
            if (!empty($matches[0])) {
                $batchPassed = count(array_filter($matches[0], fn($m) => str_contains($m, '✅') || str_contains($m, '✓')));
                $batchFailed = count(array_filter($matches[0], fn($m) => str_contains($m, '⨯') || str_contains($m, '✗')));
            } else {
                $batchFailed = count($batch);
                if ($returnCode === 0) {
                    $batchPassed = count($batch);
                    $batchFailed = 0;
                }
            }
        }
        
        // Save output
        file_put_contents($batchOutputFile, $liveOutput);
        
        // Show batch results
        echo "\n  ";
        if ($returnCode === 0 && $batchFailed === 0) {
            echo "✅ BATCH SUCCESS";
        } elseif ($batchPassed > 0) {
            echo "⚠️  PARTIAL SUCCESS";
        } else {
            echo "❌ BATCH FAILED";
        }
        
        echo " | Time: {$batchRuntime}s | Passed: {$batchPassed} | Failed: {$batchFailed}\n";
        
        // Show failed tests if verbose
        if ($batchFailed > 0 && isset($options['verbose'])) {
            echo "  ❌ Failed tests:\n";
            foreach ($testResults as $result) {
                if ($result['result'] === 'FAIL') {
                    echo "    - {$result['name']}\n";
                }
            }
        }
        
        // Store results
        $batchResult = [
            'category' => $category,
            'batch_index' => $batchIndex + 1,
            'tests' => $batch,
            'passed' => $batchPassed,
            'failed' => $batchFailed,
            'runtime' => $batchRuntime,
            'output_file' => $batchOutputFile,
            'return_code' => $returnCode,
            'test_results' => $testResults
        ];
        
        $results['batches'][] = $batchResult;
        $categoryResults['batches'][] = $batchResult;
        
        // Update totals
        $results['total'] += count($batch);
        $results['passed'] += $batchPassed;
        $results['failed'] += $batchFailed;
        $categoryResults['total'] += count($batch);
        $categoryResults['passed'] += $batchPassed;
        $categoryResults['failed'] += $batchFailed;
    }
    
    // Category summary
    $categorySuccess = round(($categoryResults['passed'] / max($categoryResults['total'], 1)) * 100, 1);
    echo "\n  📊 Category Summary:\n";
    echo "     Total: {$categoryResults['total']} | Passed: {$categoryResults['passed']} | Failed: {$categoryResults['failed']}\n";
    echo "     Success Rate: {$categorySuccess}%\n";
    
    $results['categories'][$category] = $categoryResults;
}

// Generate combined output file
$combinedOutputFile = $baseDir . '/' . $loggingConfig['combinedOutput'];
$combinedOutput = "Dusk Test Results - " . date('Y-m-d H:i:s') . "\n";
$combinedOutput .= str_repeat("=", 50) . "\n\n";

foreach ($results['batches'] as $batch) {
    $combinedOutput .= "Batch {$batch['batch_index']} - {$batch['category']} Category\n";
    $combinedOutput .= str_repeat("-", 40) . "\n";
    
    if (file_exists($batch['output_file'])) {
        $batchContent = file_get_contents($batch['output_file']);
        $lines = explode("\n", $batchContent);
        foreach ($lines as $line) {
            if (preg_match('/^\s*(✅|⨯|✓|✗|FAIL|PASS|WARNING|ERROR)/', $line)) {
                $combinedOutput .= $line . "\n";
            }
        }
    }
    $combinedOutput .= "\n";
}

file_put_contents($combinedOutputFile, $combinedOutput);

// Generate summary
$summaryFile = $baseDir . '/' . $loggingConfig['summaryOutput'];
$totalTime = round(microtime(true) - $startTime, 2);

$summary = "Dusk Test Execution Summary\n";
$summary .= "==========================\n";
$summary .= "Date: " . date('Y-m-d H:i:s') . "\n";
$summary .= "Total Runtime: {$totalTime}s\n\n";

$summary .= "Overall Results:\n";
$summary .= "Total Tests: {$results['total']}\n";
$summary .= "Passed: {$results['passed']} ✅\n";
$summary .= "Failed: {$results['failed']} ❌\n";
$summary .= "Success Rate: " . round(($results['passed'] / max($results['total'], 1)) * 100, 1) . "%\n\n";

$summary .= "Results by Category:\n";
foreach ($results['categories'] as $category => $catResults) {
    $summary .= "{$category}: {$catResults['passed']}/{$catResults['total']} passed";
    if ($catResults['failed'] > 0) {
        $summary .= " ({$catResults['failed']} failed)";
    }
    $summary .= "\n";
}

if ($results['failed'] > 0) {
    $summary .= "\nFailed Test Categories:\n";
    foreach ($results['categories'] as $category => $catResults) {
        if ($catResults['failed'] > 0) {
            $summary .= "- {$category}: {$catResults['failed']} failed tests\n";
        }
    }
}

file_put_contents($summaryFile, $summary);

// Final comprehensive summary
echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    🏁 FINAL RESULTS                         ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Tests Run: " . str_pad($results['total'], 5, " ", STR_PAD_LEFT) . "                     ║\n";
echo "║ Passed:         " . str_pad($results['passed'], 5, " ", STR_PAD_LEFT) . " ✅                    ║\n";
echo "║ Failed:         " . str_pad($results['failed'], 5, " ", STR_PAD_LEFT) . " ❌                    ║\n";
echo "║ Success Rate:    " . str_pad(round(($results['passed'] / max($results['total'], 1)) * 100, 1) . "%", 5, " ", STR_PAD_LEFT) . "                     ║\n";
echo "║ Total Runtime:   " . str_pad($totalTime . "s", 5, " ", STR_PAD_LEFT) . "                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";

echo "\n📁 Output Files:\n";
echo "   Combined Results: " . str_replace($baseDir . '/', '', $combinedOutputFile) . "\n";
echo "   Summary Report:  " . str_replace($baseDir . '/', '', $summaryFile) . "\n";

if ($results['failed'] > 0) {
    echo "\n⚠️  Some tests failed. Use --verbose to see failed test details.\n";
    echo "📝 Check individual batch files for detailed error messages.\n";
    exit(1);
} else {
    echo "\n🎉 All tests passed successfully!\n";
    echo "✨ Great job maintaining code quality!\n";
    exit(0);
}