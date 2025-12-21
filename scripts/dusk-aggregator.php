#!/usr/bin/env php
<?php

/**
 * Dusk Test Results Aggregator
 *
 * This script aggregates Dusk test results from multiple batch files:
 * 1. Discovers and lists browser test results from various folders
 * 2. Combines results and outputs to docs/testResultsDusk.txt
 * 3. Extracts only test result lines, ignoring error dumps
 * 4. Provides detailed reporting and metrics
 */

$baseDir = dirname(__DIR__);

// Command line options
$options = getopt('', [
    'input-dir:',
    'output-file:',
    'summary-file:',
    'verbose',
    'clean',
    'help'
]);

if (isset($options['help'])) {
    echo "Dusk Test Results Aggregator\n";
    echo "============================\n\n";
    echo "Usage: php scripts/dusk-aggregator.php [options]\n\n";
    echo "Options:\n";
    echo "  --input-dir <dir>     Directory containing batch result files (default: docs/DuskTestResults)\n";
    echo "  --output-file <file>  Combined output file (default: docs/testResultsDusk.txt)\n";
    echo "  --summary-file <file> Summary output file (default: docs/testSummaryDusk.txt)\n";
    echo "  --verbose             Detailed output\n";
    echo "  --clean               Clean old batch files after aggregation\n";
    echo "  --help                This help message\n\n";
    echo "This script automatically discovers and processes Dusk test batch files.\n";
    exit(0);
}

// Configuration
$inputDir = $options['input-dir'] ?? $baseDir . '/docs/DuskTestResults';
$outputFile = $options['output-file'] ?? $baseDir . '/docs/testResultsDusk.txt';
$summaryFile = $options['summary-file'] ?? $baseDir . '/docs/testSummaryDusk.txt';
$verbose = isset($options['verbose']);
$cleanAfter = isset($options['clean']);

echo "🔧 Dusk Test Results Aggregator\n";
echo "===============================\n";

// Function to find batch result files
function findBatchFiles(string $inputDir): array
{
    $batchFiles = [];
    
    if (!is_dir($inputDir)) {
        return $batchFiles;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($inputDir, FilesystemIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'txt') {
            $content = file_get_contents($file->getPathname());
            // Only include files that look like test results
            if (preg_match('/(PASS|FAIL|✅|⨯|✓|✗)/i', $content)) {
                $batchFiles[] = $file->getPathname();
            }
        }
    }
    
    sort($batchFiles);
    return $batchFiles;
}

// Function to parse test results from file content
function parseTestResults(string $content): array
{
    $lines = explode("\n", $content);
    $results = [];
    $currentTestClass = null;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and warnings
        if (empty($line) || 
            str_starts_with($line, 'Warning:') || 
            str_starts_with($line, 'Note:') ||
            str_starts_with($line, 'PHPUnit')) {
            continue;
        }
        
        // Detect test class header (format: "   FAIL  Tests\Browser\ClassName")
        if (preg_match('/^\s*(PASS|FAIL)\s+Tests\\\\Browser\\\\(.+)$/i', $line, $matches)) {
            $currentTestClass = $matches[2];
            $classStatus = strtoupper($matches[1]);
            $results[$currentTestClass] = [
                'class' => $currentTestClass,
                'class_status' => $classStatus,
                'tests' => []
            ];
            continue;
        }
        
        // Detect individual test results - simplified approach
        // Look for any line that ends with duration (number + 's')
        if (preg_match('/^(.*?)\s+([\d.]+)s\s*$/', $line, $matches)) {
            $testPart = $matches[1];
            $duration = floatval($matches[2]);
            
            // Extract test name and symbol more carefully
            // Skip if this looks like an error message rather than test result
            if (str_contains($testPart, 'Expected') || 
                str_contains($testPart, 'Failed') ||
                str_contains($testPart, 'at ')) {
                continue;
            }
            
            // Find the first non-whitespace character (symbol)
            $symbol = '';
            $testName = '';
            for ($i = 0; $i < mb_strlen($testPart); $i++) {
                $char = mb_substr($testPart, $i, 1);
                if ($symbol === '' && trim($char) !== '') {
                    $symbol = $char;
                } elseif ($symbol !== '' && trim($char) !== '') {
                    $testName .= $char;
                }
            }
            
            $testName = trim($testName);
            
            // If we have a test name and symbol, record it
            if (!empty($testName) && !empty($symbol)) {
                // Use simple heuristics to determine pass/fail
                // For now, treat everything as FAIL since we're seeing ⨯ symbols
                // In real runs with passing tests, these would be ✅ or ✓ symbols
                $passSymbols = ['✅', '✓', '✔', "\xE2\x9C\x85", "\xE2\x9C\x93"]; // Include Unicode check marks
                $status = in_array($symbol, $passSymbols) ? 'PASS' : 'FAIL';
                
                if ($currentTestClass) {
                    $results[$currentTestClass]['tests'][] = [
                        'name' => $testName,
                        'status' => $status,
                        'duration' => $duration,
                        'symbol' => $symbol
                    ];
                }
            }
            continue;
        }
        
        // Skip error details, stack traces, etc.
        if (str_contains($line, '---') || 
            str_contains($line, 'at ') ||
            str_contains($line, '➜') ||
            str_contains($line, '▕') ||
            str_contains($line, 'Expected') ||
            str_contains($line, 'Failed asserting') ||
            str_contains($line, 'vendor frames') ||
            str_starts_with($line, '+') ||
            preg_match('/^\s*\d+\s+\w+/', $line) ||
            str_contains($line, 'FAILED') && !str_contains($line, 'Tests\\Browser\\')) {
            continue;
        }
    }
    
    return $results;
}

// Find all batch files
$batchFiles = findBatchFiles($inputDir);

if (empty($batchFiles)) {
    echo "❌ No batch result files found in: {$inputDir}\n";
    exit(1);
}

echo "Found " . count($batchFiles) . " batch files to process\n";

if ($verbose) {
    foreach ($batchFiles as $file) {
        echo "  - " . str_replace($baseDir . '/', '', $file) . "\n";
    }
}

// Process each batch file
$allResults = [];
$totalTests = 0;
$totalPassed = 0;
$totalFailed = 0;
$totalDuration = 0;

foreach ($batchFiles as $batchFile) {
    if ($verbose) {
        echo "Processing: " . basename($batchFile) . "\n";
    }
    
    $content = file_get_contents($batchFile);
    $batchResults = parseTestResults($content);
    
    foreach ($batchResults as $classResult) {
        $className = $classResult['class'];
        
        if (!isset($allResults[$className])) {
            $allResults[$className] = [
                'class' => $className,
                'class_status' => $classResult['class_status'],
                'tests' => [],
                'source_files' => []
            ];
        }
        
        $allResults[$className]['tests'] = array_merge(
            $allResults[$className]['tests'],
            $classResult['tests']
        );
        
        if (!in_array($batchFile, $allResults[$className]['source_files'])) {
            $allResults[$className]['source_files'][] = $batchFile;
        }
    }
}

// Calculate totals
foreach ($allResults as $classResult) {
    foreach ($classResult['tests'] as $test) {
        $totalTests++;
        $totalDuration += $test['duration'];
        if ($test['status'] === 'PASS') {
            $totalPassed++;
        } else {
            $totalFailed++;
        }
    }
}

echo "Processed " . count($allResults) . " test classes\n";
echo "Total: {$totalTests} tests ({$totalPassed} passed, {$totalFailed} failed)\n";

// Generate combined output
$combinedOutput = "Dusk Test Results - " . date('Y-m-d H:i:s') . "\n";
$combinedOutput .= str_repeat("=", 60) . "\n";
$combinedOutput .= "Summary: {$totalPassed}/{$totalTests} tests passed (" . 
                 round(($totalPassed / max($totalTests, 1)) * 100, 1) . "%)\n";
$combinedOutput .= "Total Duration: " . round($totalDuration, 2) . "s\n";
$combinedOutput .= str_repeat("=", 60) . "\n\n";

foreach ($allResults as $className => $classResult) {
    $classStatus = $classResult['class_status'];
    $classStatusSymbol = $classStatus === 'PASS' ? '✅' : '❌';
    $testCount = count($classResult['tests']);
    $passedCount = count(array_filter($classResult['tests'], fn($t) => $t['status'] === 'PASS'));
    
    $combinedOutput .= "{$classStatusSymbol} {$classStatus} {$className}\n";
    
    foreach ($classResult['tests'] as $test) {
        $combinedOutput .= "   {$test['symbol']} {$test['name']}\n";
    }
    
    $combinedOutput .= "   ──────────────────────────────────────────────────────────────\n";
    $combinedOutput .= "   Summary: {$passedCount}/{$testCount} tests passed\n\n";
}

// Ensure output directory exists
$outputDir = dirname($outputFile);
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Save combined output
file_put_contents($outputFile, $combinedOutput);
echo "Combined results saved to: " . str_replace($baseDir . '/', '', $outputFile) . "\n";

// Generate summary
$summary = "Dusk Test Execution Summary\n";
$summary .= "==========================\n";
$summary .= "Date: " . date('Y-m-d H:i:s') . "\n";
$summary .= "Batch Files Processed: " . count($batchFiles) . "\n";
$summary .= "Test Classes: " . count($allResults) . "\n";
$summary .= "Total Tests: {$totalTests}\n";
$summary .= "Passed: {$totalPassed} ✅\n";
$summary .= "Failed: {$totalFailed} ❌\n";
$summary .= "Success Rate: " . round(($totalPassed / max($totalTests, 1)) * 100, 1) . "%\n";
$summary .= "Total Duration: " . round($totalDuration, 2) . "s\n";
$summary .= "Average Test Duration: " . round($totalDuration / max($totalTests, 1), 2) . "s\n\n";

$summary .= "Results by Class:\n";
foreach ($allResults as $className => $classResult) {
    $testCount = count($classResult['tests']);
    $passedCount = count(array_filter($classResult['tests'], fn($t) => $t['status'] === 'PASS'));
    $status = $passedCount === $testCount ? '✅' : '❌';
    $summary .= "{$status} {$className}: {$passedCount}/{$testCount} passed\n";
}

if ($totalFailed > 0) {
    $summary .= "\nFailed Tests:\n";
    foreach ($allResults as $className => $classResult) {
        $failedTests = array_filter($classResult['tests'], fn($t) => $t['status'] === 'FAIL');
        if (!empty($failedTests)) {
            foreach ($failedTests as $test) {
                $summary .= "❌ {$className} > {$test['name']}\n";
            }
        }
    }
}

$summary .= "\nSource Files:\n";
foreach ($batchFiles as $batchFile) {
    $relativePath = str_replace($baseDir . '/', '', $batchFile);
    $summary .= "- {$relativePath}\n";
}

// Save summary
file_put_contents($summaryFile, $summary);
echo "Summary saved to: " . str_replace($baseDir . '/', '', $summaryFile) . "\n";

// Clean old batch files if requested
if ($cleanAfter) {
    echo "\n🧹 Cleaning up batch files...\n";
    foreach ($batchFiles as $batchFile) {
        if (unlink($batchFile)) {
            echo "  Deleted: " . basename($batchFile) . "\n";
        }
    }
}

echo "\n✅ Aggregation completed successfully!\n";

if ($totalFailed > 0) {
    echo "⚠️  {$totalFailed} tests failed. Check the detailed results for more information.\n";
    exit(1);
} else {
    echo "🎉 All tests passed!\n";
    exit(0);
}