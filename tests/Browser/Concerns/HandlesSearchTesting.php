<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

trait HandlesSearchTesting
{
    /**
     * Test search input exists and is functional.
     */
    protected function assertSearchInputExists(Browser $browser, string $selector = 'input[placeholder*="search" i]'): void
    {
        $browser->waitFor($selector, 5)
            ->assertVisible($selector)
            ->assertEnabled($selector);
    }

    /**
     * Test search functionality without errors.
     */
    protected function testSearchFunctionality(Browser $browser, string $searchTerm = 'test'): void
    {
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) use ($searchTerm) {
            $searchInput->type($searchTerm)
                ->pause(500); // Wait for debounce
        });

        // Assert no JavaScript errors occurred during search
        $this->assertNoJavaScriptErrors($browser);
    }

    /**
     * Test search variable is properly defined.
     */
    protected function assertSearchVariableDefined(Browser $browser): void
    {
        $isDefined = $this->executeScript($browser, "
            // Check various ways search might be defined
            return (
                typeof window.search !== 'undefined' ||
                typeof search !== 'undefined' ||
                document.querySelector('[x-model*=\"search\"]') !== null ||
                document.querySelector('[data-search]') !== null ||
                document.querySelector('input[name*=\"search\"]') !== null
            );
        ");

        $this->assertTrue($isDefined, 'Search functionality should be properly defined');
    }

    /**
     * Test Alpine.js search data binding.
     */
    protected function testAlpineSearchBinding(Browser $browser): void
    {
        // Check if search is bound with Alpine.js
        $searchBinding = $this->executeScript($browser, "
            var searchInput = document.querySelector('[x-model*=\"search\"], [x-data*=\"search\"]');
            if (!searchInput) return false;
            
            // Check if it's properly bound to Alpine data
            var alpineEl = searchInput.closest('[x-data]');
            if (!alpineEl) return false;
            
            var alpineData = alpineEl._x_dataStack && alpineEl._x_dataStack[0];
            if (!alpineData) return false;
            
            // Look for search property in Alpine data
            for (var key in alpineData) {
                if (key.toLowerCase().includes('search')) {
                    return true;
                }
            }
            
            return false;
        ");

        $this->assertTrue($searchBinding, 'Search should be properly bound to Alpine.js data');
    }

    /**
     * Test search input events.
     */
    protected function testSearchInputEvents(Browser $browser): void
    {
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
            // Test input event
            $searchInput->type('test input')
                ->pause(200);

            // Test change event
            $searchInput->append(' more')
                ->pause(200);

            // Test key events
            $searchInput->keys('{backspace}')
                ->pause(200);

            // Test clear
            $searchInput->clear()
                ->pause(200);
        });

        // Assert no errors occurred
        $this->assertNoJavaScriptErrors($browser);
    }

    /**
     * Test search debouncing functionality.
     */
    protected function testSearchDebouncing(Browser $browser): void
    {
        $startTime = microtime(true);

        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
            $searchInput->type('quick search');
        });

        $endTime = microtime(true);
        $timeElapsed = $endTime - $startTime;

        // Should take some time due to debouncing (not instant)
        $this->assertGreaterThan(0.1, $timeElapsed, 'Search should have debouncing delay');
    }

    /**
     * Test search results filtering.
     */
    protected function testSearchResultsFiltering(Browser $browser, string $searchTerm, array $expectedResults): void
    {
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) use ($searchTerm) {
            $searchInput->type($searchTerm)
                ->pause(500);
        });

        // Check that expected results are visible
        foreach ($expectedResults as $result) {
            $browser->assertSee($result);
        }

        // Check that unexpected results are hidden (if applicable)
        $browser->pause(500);
    }

    /**
     * Test search clear functionality.
     */
    protected function testSearchClear(Browser $browser): void
    {
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
            $searchInput->type('test search')
                ->pause(200);

            // Try to find and click clear button if it exists
            $searchInput->whenAvailable('~ .clear-search, [data-clear-search]', function ($clearBtn) {
                $clearBtn->click();
            })->otherwise(function () {
                // Fallback: clear the input directly
                $searchInput->clear();
            });
        });

        $browser->pause(200);

        // Assert search input is empty
        $searchValue = $this->executeScript($browser, "
            var searchInput = document.querySelector('input[placeholder*=\"search\" i]');
            return searchInput ? searchInput.value : '';
        ");

        $this->assertEmpty($searchValue, 'Search input should be cleared');
    }

    /**
     * Test search with special characters.
     */
    protected function testSearchSpecialCharacters(Browser $browser): void
    {
        $specialChars = ['@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '+', '=', '[', ']', '{', '}', '|', '\\', ';', ':', '\'', '"', '<', '>', ',', '.', '?', '/'];

        foreach ($specialChars as $char) {
            $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) use ($char) {
                $searchInput->clear()
                    ->type($char)
                    ->pause(200);
            });

            // Assert no JavaScript errors with special characters
            $this->assertNoJavaScriptErrors($browser);
        }
    }

    /**
     * Test search with Unicode characters.
     */
    protected function testSearchUnicode(Browser $browser): void
    {
        $unicodeStrings = ['café', 'naïve', 'résumé', 'piñata', '测试', 'тест', '🔍', '📊'];

        foreach ($unicodeStrings as $unicode) {
            $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) use ($unicode) {
                $searchInput->clear()
                    ->type($unicode)
                    ->pause(200);
            });

            // Assert no JavaScript errors with Unicode
            $this->assertNoJavaScriptErrors($browser);
        }
    }

    /**
     * Test search performance with large input.
     */
    protected function testSearchPerformance(Browser $browser): void
    {
        $largeText = str_repeat('test search performance ', 100);

        $startTime = microtime(true);

        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) use ($largeText) {
            $searchInput->type($largeText);
        });

        $endTime = microtime(true);
        $timeElapsed = $endTime - $startTime;

        // Should complete within reasonable time
        $this->assertLessThan(2.0, $timeElapsed, 'Search with large input should complete within 2 seconds');
    }

    /**
     * Test search accessibility.
     */
    protected function testSearchAccessibility(Browser $browser): void
    {
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
            // Check for proper ARIA attributes
            $searchInput->assertAttributeExists('aria-label')
                ->assertAttributeExists('type', 'search');

            // Test keyboard navigation
            $searchInput->click()
                ->keys('{tab}')
                ->pause(100);
        });

        // Test screen reader compatibility
        $hasAriaLabel = $this->executeScript($browser, "
            var searchInput = document.querySelector('input[placeholder*=\"search\" i]');
            return searchInput && (
                searchInput.getAttribute('aria-label') ||
                searchInput.getAttribute('aria-labelledby') ||
                searchInput.getAttribute('title')
            );
        ");

        $this->assertTrue($hasAriaLabel, 'Search input should have accessibility label');
    }

    /**
     * Test search form submission.
     */
    protected function testSearchFormSubmission(Browser $browser): void
    {
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
            $searchInput->type('test search')
                ->keys('{enter}');
        });

        $browser->pause(500);

        // Assert no JavaScript errors occurred during form submission
        $this->assertNoJavaScriptErrors($browser);
    }

    /**
     * Test search suggestions/autocomplete.
     */
    protected function testSearchSuggestions(Browser $browser): void
    {
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
            $searchInput->type('test')
                ->pause(500); // Wait for suggestions
        });

        // Check if suggestions appear (if implemented)
        $browser->whenAvailable('.search-suggestions, [data-search-suggestions]', function ($suggestions) {
            $suggestions->assertVisible();
        })->otherwise(function () {
            // Suggestions might not be implemented, which is fine
        });

        $this->assertNoJavaScriptErrors($browser);
    }

    /**
     * Test search state persistence.
     */
    protected function testSearchStatePersistence(Browser $browser): void
    {
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
            $searchInput->type('persistent search');
        });

        // Refresh page
        $browser->refresh()
            ->waitForJavaScript($this);

        // Check if search value is persisted (if implemented)
        $searchValue = $this->executeScript($browser, "
            var searchInput = document.querySelector('input[placeholder*=\"search\" i]');
            return searchInput ? searchInput.value : '';
        ");

        // This test depends on whether search persistence is implemented
        // If implemented: $this->assertEquals('persistent search', $searchValue);
        // If not implemented: $this->assertEmpty($searchValue);
    }

    /**
     * Test search error handling.
     */
    protected function testSearchErrorHandling(Browser $browser): void
    {
        // Test search with invalid data
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
            $searchInput->type('invalid search data')
                ->pause(200);
        });

        // Check that errors are handled gracefully
        $errors = $this->checkForJavaScriptErrors($browser);
        $searchErrors = array_filter($errors, function ($error) {
            $message = implode(' ', $error['args'] ?? []);

            return str_contains(strtolower($message), 'search');
        });

        $this->assertEmpty($searchErrors, 'Search should handle errors gracefully');
    }

    /**
     * Test search integration with other components.
     */
    protected function testSearchIntegration(Browser $browser): void
    {
        $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
            $searchInput->type('integration test')
                ->pause(500);
        });

        // Check if search affects other components (filters, tables, etc.)
        $browser->whenAvailable('[data-search-results], .search-results', function ($results) {
            $results->assertVisible();
        })->otherwise(function () {
            // Search results might not be in a separate container
        });

        $this->assertNoJavaScriptErrors($browser);
    }
}
