<?php

namespace Tests\Browser\HR;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\JavaScriptDuskTestCase;

class HRDebugTest extends JavaScriptDuskTestCase
{
    /**
     * Debug test to see what's actually on HR pages.
     */
    public function test_debug_hr_pages(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $organization->users()->attach($user->id, [
                'roles' => json_encode(['admin']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Login and visit home page first
            $browser->loginAs($user)
                ->visit('/')
                ->pause(3000)
                ->screenshot('debug-home-page')
                ->dump('Home page loaded');

            // Now try HR pages
            $browser->visit('/hr/employees')
                ->pause(3000)
                ->screenshot('debug-hr-employees')
                ->dump('HR employees page loaded');

            // Check what's on the page
            $pageTitle = $browser->text('h1, h2, .text-2xl, title');
            $browser->dump("Page title/content: " . $pageTitle);

            // Look for any forms or inputs
            $inputs = $browser->elements('input');
            $browser->dump("Number of inputs found: " . count($inputs));

            // Look for links
            $links = $browser->elements('a');
            $browser->dump("Number of links found: " . count($links));

            // Get page source for debugging
            $pageSource = $browser->driver->getPageSource();
            $browser->dump("Page contains Employee Management: " . (strpos($pageSource, 'Employee Management') !== false));
            $browser->dump("Page contains Add Employee: " . (strpos($pageSource, 'Add Employee') !== false));

            // Try create page
            $browser->visit('/hr/employees/create')
                ->pause(3000)
                ->screenshot('debug-hr-employees-create')
                ->dump('HR employees create page loaded');

            // Check for form elements on create page
            $createInputs = $browser->elements('input');
            $browser->dump("Number of inputs on create page: " . count($createInputs));

            // Get input names
            $inputNames = [];
            foreach ($createInputs as $input) {
                $name = $input->getAttribute('name');
                if ($name) {
                    $inputNames[] = $name;
                }
            }
            $browser->dump("Input names found: " . json_encode($inputNames));
        });
    }
}