<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TestSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:summarize {file=testResults.txt : The path to the test output file.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads a test output file and generates a summary showing top failing tests grouped by module with professional, non-truncated formatting.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!File::exists($filePath)) {
            $this->error("Error: Test results file not found at: {$filePath}");
            return Command::FAILURE;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Process all lines to get statistics and detailed information
        $results = $this->processLines($lines);

        // Output the summary using professional console formatting
        $this->outputSummary($results);

        return Command::SUCCESS;
    }

    /**
     * Processes the lines of the test output file to gather statistics and detailed failure information.
     *
     * @param array $lines
     * @return array
     */
    protected function processLines(array $lines): array
    {
        $currentClass = null;
        $classSummary = [];
        $detailedResults = []; // Stores Class => ['failures' => [], 'warnings' => []]
        $overall = ['passed' => 0, 'failed' => 0, 'warned' => 0, 'total' => 0];

        foreach ($lines as $line) {
            $line = trim($line);

            // 1. Detect Class Header (PASS, FAIL, WARN, FAILED)
            // We look for a line starting with status, followed by text containing '\' and 'Test' to capture the FQCN.
            if (preg_match('/^\h*(PASS|FAIL|WARN|FAILED)\h+(.+)/u', $line, $matches)) {
                $status = $matches[1];
                $fullHeader = trim($matches[2]);

                // Extract the fully qualified class name (FQCN) from the header line
                if (preg_match('/(\b[A-Za-z0-9\\\\]*Test)\b/', $fullHeader, $classMatches)) {
                    $currentClass = $classMatches[1];
                } else {
                    // This is usually a second line of a failure or a summary line, ignore it
                    $currentClass = null;
                    continue;
                }

                // Initialize class summary
                if ($currentClass && !isset($classSummary[$currentClass])) {
                    $shortName = $this->getShortModuleName($currentClass);
                    $classSummary[$currentClass] = ['shortName' => $shortName, 'passed' => 0, 'failed' => 0, 'warned' => 0, 'total' => 0];
                    $detailedResults[$currentClass] = ['failures' => [], 'warnings' => []];
                }
            }

            // Must have a current class to count test cases
            if (!$currentClass) {
                continue;
            }

            // 2. Detect Test Case Results (✓, ⨯, -)
            // Using \h* (horizontal whitespace) and the 'u' (Unicode) modifier for robust parsing of symbols.
            if (preg_match('/^\h*([✓⨯-])\h*(.+)/u', $line, $matches)) {
                $symbol = $matches[1];
                $testCaseWithTime = trim($matches[2]);

                // Remove the trailing time suffix (e.g., ' 0.26s')
                $testName = preg_replace('/\h+\d+\.\d+s\h*$/u', '', $testCaseWithTime);

                $classSummary[$currentClass]['total']++;
                $overall['total']++;

                if ($symbol === '✓') {
                    $classSummary[$currentClass]['passed']++;
                    $overall['passed']++;
                } elseif ($symbol === '⨯') {
                    $classSummary[$currentClass]['failed']++;
                    $overall['failed']++;
                    $detailedResults[$currentClass]['failures'][] = $testName;
                } elseif ($symbol === '-') {
                    $classSummary[$currentClass]['warned']++;
                    $overall['warned']++;
                    // Remove the common ' -> ' skipped/warned message from the test name for cleaner output
                    $cleanTestName = Str::before($testName, ' -> ');
                    $detailedResults[$currentClass]['warnings'][] = $cleanTestName;
                }
            }
        }

        return ['summary' => $classSummary, 'detailed' => $detailedResults, 'overall' => $overall];
    }

    /**
     * Extracts the short module name from the fully qualified test class name.
     * e.g., 'Tests\Feature\Portal\EmployeePortalTest' -> 'EmployeePortal'
     *
     * @param string $className
     * @return string
     */
    protected function getShortModuleName(string $className): string
    {
        // Get the last segment (class name)
        $shortName = basename(str_replace('\\', '/', $className));

        // Remove 'Test' suffix
        if (str_ends_with($shortName, 'Test')) {
            $shortName = substr($shortName, 0, -4);
        }

        return $shortName;
    }

    /**
     * Outputs the test summary using Laravel console components, prioritizing lists for long descriptions.
     *
     * @param array $results
     * @return void
     */
    protected function outputSummary(array $results): void
    {
        $overall = $results['overall'];
        $summary = $results['summary'];
        $detailed = $results['detailed'];

        $this->info(str_repeat('=', 60));
        $this->info("✨ Test Execution Summary - Professional Report v2.0 ✨");
        $this->info(str_repeat('=', 60));

        //
        // 1. Overall Results (Table is fine here as data is short)
        //
        $this->line("\n--- Overall Test Status ---");
        if ($overall['total'] > 0) {
            $data = [
                ['Total Tests', (string)$overall['total']],
                ['Passed', "{$overall['passed']} (" . round(($overall['passed'] / $overall['total']) * 100, 1) . '%)'],
                ['Failed', "{$overall['failed']} (" . round(($overall['failed'] / $overall['total']) * 100, 1) . '%)'],
                ['Warnings/Skipped', "{$overall['warned']} (" . round(($overall['warned'] / $overall['total']) * 100, 1) . '%)'],
            ];
            $this->table(['Metric', 'Count/Percentage'], $data);
        } else {
            $this->warn("No tests were found to process.");
            return;
        }

        //
        // 2. Failed Tests Details (Using list format to prevent truncation)
        //
        if ($overall['failed'] > 0) {
            $this->error("\n--- ❌ Detailed Failures ({$overall['failed']} Total) ---");
            $this->line(''); // Add a separator line
            foreach ($summary as $class => $data) {
                if ($data['failed'] > 0) {
                    $failCount = $data['failed'];
                    $totalCount = $data['total'];
                    // Print module header
                    $this->line(" <fg=red;options=bold>{$data['shortName']} ({$failCount}/{$totalCount} failures):</>");

                    // Print detailed test cases
                    foreach ($detailed[$class]['failures'] as $testName) {
                        $this->line("   <fg=red>⨯ {$testName}</>");
                    }
                    $this->line(''); // Blank line for separation
                }
            }
        } else {
            $this->info("\n--- ✅ All Tests Passed! ---");
        }

        //
        // 3. Warnings/Skipped Tests (Using list format to prevent truncation)
        //
        if ($overall['warned'] > 0) {
            $this->warn("\n--- ⚠️ Warnings/Skipped Tests ({$overall['warned']} Total) ---");
            $this->line(''); // Add a separator line
            foreach ($summary as $class => $data) {
                if ($data['warned'] > 0) {
                    $warnCount = $data['warned'];
                    $totalCount = $data['total'];
                    // Print module header
                    $this->line(" <fg=yellow;options=bold>{$data['shortName']} ({$warnCount}/{$totalCount} warnings):</>");

                    // Print detailed test cases
                    foreach ($detailed[$class]['warnings'] as $testName) {
                        $this->line("   <fg=yellow>- {$testName}</>");
                    }
                    $this->line(''); // Blank line for separation
                }
            }
        }

        //
        // 4. Passed Modules Summary
        //
        $passedModules = collect($summary)->filter(fn($data) => $data['passed'] == $data['total'] && $data['total'] > 0);
        if ($passedModules->isNotEmpty()) {
            $this->line("\n--- ✅ Fully Passed Modules ---");
            $passedData = $passedModules->map(fn($data) => [
                "<fg=green>{$data['shortName']}</>",
                "{$data['passed']}/{$data['total']}"
            ])->toArray();
            $this->table(['Module', 'Tests Passed'], $passedData);
        }
    }
}
