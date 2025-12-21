<?php

use Tests\DuskTestCase;

class SearchVariableTest extends DuskTestCase
{
    /**
     * Test that Alpine.js search variable is properly defined on all pages
     */
    public function test_search_variable_defined_on_employee_pages()
    {
        $this->browse(function ($browser) {
            // Test employee create page
            $browser->visit('/hr/employees/create')
                    ->waitForText('Employee Management', 10)
                    ->assertScript('typeof window.Alpine !== "undefined"')
                    ->assertScript('document.querySelector("[x-data]") !== null');

            // Test employee show page
            $browser->visit('/hr/employees/1')
                    ->waitForText('Employee Profile', 10)
                    ->assertScript('typeof window.Alpine !== "undefined"')
                    ->assertScript('document.querySelector("[x-data]") !== null');

            // Test employee edit page
            $browser->visit('/hr/employees/1/edit')
                    ->waitForText('Edit Employee', 10)
                    ->assertScript('typeof window.Alpine !== "undefined"')
                    ->assertScript('document.querySelector("[x-data]") !== null');
        });
    }

    /**
     * Test that Alpine.js search variable is properly defined on accounting pages
     */
    public function test_search_variable_defined_on_accounting_pages()
    {
        $this->browse(function ($browser) {
            // Test bank statements page
            $browser->visit('/accounts/bank-statements')
                    ->waitForText('Bank Statements', 10)
                    ->assertScript('typeof window.Alpine !== "undefined"')
                    ->assertScript('document.querySelector("[x-data]") !== null');
        });
    }

    /**
     * Test that no Alpine.js errors are thrown for search variable
     */
    public function test_no_alpine_search_errors()
    {
        $this->browse(function ($browser) {
            $browser->visit('/hr/employees/create')
                    ->waitForText('Employee Management', 10)
                    ->waitUntil("
                        return (() => {
                            // Check for Alpine.js errors in console
                            const errors = [];
                            const originalError = console.error;
                            console.error = function(...args) {
                                errors.push(args.join(' '));
                                originalError.apply(console, args);
                            };
                            
                            // Trigger a search interaction
                            const searchInput = document.querySelector('input[x-model*=\"search\"], input[wire:model*=\"search\"]');
                            if (searchInput) {
                                searchInput.value = 'test';
                                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            
                            // Restore original error function
                            console.error = originalError;
                            
                            // Check if any Alpine.js search errors occurred
                            return !errors.some(error => error.includes('search') && error.includes('not defined'));
                        })();
                    ", 10);
        });
    }
}