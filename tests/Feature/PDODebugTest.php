<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase;

class PDODebugTest extends TestCase
{
    public function testFindPDOError()
    {
        // Set up error handler to catch the deprecation
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            echo "\n=== PDO ERROR FOUND ===\n";
            echo "Error: $errstr\n";
            echo "File: $errfile\n";
            echo "Line: $errline\n";

            // Get the backtrace
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            echo "\nBacktrace:\n";
            foreach ($trace as $i => $frame) {
                if (isset($frame['file'])) {
                    echo "#$i {$frame['file']}:{$frame['line']}";
                    if (isset($frame['class'])) {
                        echo " {$frame['class']}{$frame['type']}{$frame['function']}";
                    }
                    echo "\n";
                }
            }

            restore_error_handler();
            return true;
        });

        // Trigger Laravel boot
        $this->app->make('config');

        restore_error_handler();
        $this->assertTrue(true);
    }
}
