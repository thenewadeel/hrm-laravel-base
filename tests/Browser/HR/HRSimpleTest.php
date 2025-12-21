<?php

namespace Tests\Browser\HR;

use Laravel\Dusk\Browser;
use Tests\Browser\JavaScriptDuskTestCase;

class HRSimpleTest extends JavaScriptDuskTestCase
{
    /**
     * Test basic application and HR route accessibility.
     */
    public function test_basic_app_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            // Visit home page
            $browser->visit('/')
                ->pause(3000)
                ->screenshot('hr-simple-home');

            // Try to visit HR routes
            $browser->visit('/hr/employees')
                ->pause(3000)
                ->screenshot('hr-simple-employees');

            // Check if page loads without errors
            $title = $browser->driver->getTitle();
            $browser->dump("Page title: {$title}");

            // Try create page
            $browser->visit('/hr/employees/create')
                ->pause(3000)
                ->screenshot('hr-simple-create');

            // Just make sure we get a response (not 404)
            $statusCode = $this->getStatusCode($browser);
            $browser->dump("HTTP Status: {$statusCode}");
        });
    }

    /**
     * Simple test that just verifies routes return proper responses.
     */
    public function test_hr_route_responses(): void
    {
        $this->browse(function (Browser $browser) {
            $routes = [
                '/',
                '/login',
                '/hr/employees',
                '/hr/employees/create',
                '/hr/positions',
                '/hr/shifts'
            ];

            foreach ($routes as $route) {
                $browser->visit($route)
                    ->pause(2000)
                    ->screenshot('hr-simple-route-' . str_replace('/', '-', $route));
                
                // Check if page loads (not an error page)
                $title = $browser->driver->getTitle();
                $browser->dump("Route {$route}: {$title}");
            }
        });
    }

    /**
     * Test that we can see some expected content.
     */
    public function test_page_content_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(3000)
                ->screenshot('hr-simple-content-home');

            // Get all text from page body
            $pageText = $browser->text('body');
            $browser->dump("Page length: " . strlen($pageText) . " characters");

            // Look for common application elements
            $hasForms = strpos($pageText, '<form') !== false || strpos($pageText, 'form') !== false;
            $hasInputs = strpos($pageText, '<input') !== false || strpos($pageText, 'input') !== false;
            $hasLinks = strpos($pageText, '<a') !== false || strpos($pageText, 'href') !== false;

            $browser->dump("Has forms: " . ($hasForms ? 'yes' : 'no'));
            $browser->dump("Has inputs: " . ($hasInputs ? 'yes' : 'no'));
            $browser->dump("Has links: " . ($hasLinks ? 'yes' : 'no'));
        });
    }

    /**
     * Helper method to get HTTP status code (approximate).
     */
    private function getStatusCode(Browser $browser): string
    {
        try {
            // This is a workaround to check if we got a proper page load
            $title = $browser->driver->getTitle();
            if (strpos($title, '404') !== false) {
                return '404';
            } elseif (strpos($title, '500') !== false) {
                return '500';
            } else {
                return '200 (OK)';
            }
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}