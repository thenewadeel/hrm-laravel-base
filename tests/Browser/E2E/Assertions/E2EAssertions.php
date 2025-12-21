<?php

namespace Tests\Browser\E2E\Assertions;

use Laravel\Dusk\Browser;

class E2EAssertions
{
    /**
     * Assert workflow step is completed successfully.
     */
    public static function assertWorkflowStepCompleted(Browser $browser, string $stepName): void
    {
        $browser->assertPresent("[data-workflow-step='{$stepName}'][data-status='completed']")
            ->assertSeeIn("[data-workflow-step='{$stepName}'] .step-status", 'Completed');
    }

    /**
     * assert workflow step is in progress.
     */
    public static function assertWorkflowStepInProgress(Browser $browser, string $stepName): void
    {
        $browser->assertPresent("[data-workflow-step='{$stepName}'][data-status='in-progress']")
            ->assertSeeIn("[data-workflow-step='{$stepName}'] .step-status", 'In Progress');
    }

    /**
     * Assert workflow step failed with specific error.
     */
    public static function assertWorkflowStepFailed(Browser $browser, string $stepName, string $errorMessage): void
    {
        $browser->assertPresent("[data-workflow-step='{$stepName}'][data-status='failed']")
            ->assertSeeIn("[data-workflow-step='{$stepName}'] .error-message", $errorMessage);
    }

    /**
     * Assert financial transaction is recorded correctly.
     */
    public static function assertFinancialTransaction(Browser $browser, array $transactionData): void
    {
        $browser->assertSeeIn('[data-transaction-id]', $transactionData['id'])
            ->assertSeeIn('[data-transaction-amount]', number_format($transactionData['amount'], 2))
            ->assertSeeIn('[data-transaction-type]', $transactionData['type'])
            ->assertSeeIn('[data-transaction-date]', $transactionData['date']);
    }

    /**
     * Assert double-entry bookkeeping is balanced.
     */
    public static function assertDoubleEntryBalanced(Browser $browser, string $transactionId): void
    {
        $debitTotal = $browser->text("[data-transaction='{$transactionId}'] [data-debit-total]");
        $creditTotal = $browser->text("[data-transaction='{$transactionId}'] [data-credit-total]");
        
        // Remove formatting and compare
        $debitAmount = (float) str_replace(['$', ','], '', $debitTotal);
        $creditAmount = (float) str_replace(['$', ','], '', $creditTotal);
        
        if (abs($debitAmount - $creditAmount) > 0.01) {
            throw new \Exception("Double entry not balanced: Debit {$debitAmount} != Credit {$creditAmount}");
        }
    }

    /**
     * Assert employee payroll calculation is correct.
     */
    public static function assertPayrollCalculation(Browser $browser, array $payrollData): void
    {
        $browser->assertSeeIn('[data-gross-salary]', number_format($payrollData['gross_salary'], 2))
            ->assertSeeIn('[data-net-salary]', number_format($payrollData['net_salary'], 2))
            ->assertSeeIn('[data-total-deductions]', number_format($payrollData['total_deductions'], 2));

        if (isset($payrollData['tax_amount'])) {
            $browser->assertSeeIn('[data-tax-amount]', number_format($payrollData['tax_amount'], 2));
        }
    }

    /**
     * Assert inventory stock levels are accurate.
     */
    public static function assertInventoryStockLevel(Browser $browser, string $itemName, int $expectedQuantity): void
    {
        $browser->assertSeeIn("[data-item='{$itemName}'] [data-stock-quantity]", $expectedQuantity);
    }

    /**
     * Assert inventory transaction is recorded.
     */
    public static function assertInventoryTransaction(Browser $browser, array $transactionData): void
    {
        $browser->assertSeeIn('[data-transaction-reference]', $transactionData['reference'])
            ->assertSeeIn('[data-transaction-type]', $transactionData['type'])
            ->assertSeeIn('[data-transaction-quantity]', $transactionData['quantity'])
            ->assertSeeIn('[data-transaction-item]', $transactionData['item_name']);
    }

    /**
     * Assert multi-tenant data isolation.
     */
    public static function assertDataIsolation(Browser $browser, int $organizationId): void
    {
        $browser->assertPresent("[data-organization-id='{$organizationId}']")
            ->assertDontSeeAnyText(['Data from other organization', 'Unauthorized access']);
    }

    /**
     * Assert user permissions are enforced.
     */
    public static function assertUserPermissions(Browser $browser, array $allowedActions, array $deniedActions): void
    {
        foreach ($allowedActions as $action) {
            $browser->assertPresent("[data-action='{$action}']:not([disabled])");
        }

        foreach ($deniedActions as $action) {
            $browser->assertMissing("[data-action='{$action}']:not([disabled])");
        }
    }

    /**
     * Assert form validation works correctly.
     */
    public static function assertFormValidation(Browser $browser, array $validationErrors): void
    {
        foreach ($validationErrors as $field => $errorMessage) {
            $browser->assertSeeIn("[data-field='{$field}'] .error-message", $errorMessage);
        }
    }

    /**
     * Assert success message is displayed.
     */
    public static function assertSuccessMessage(Browser $browser, string $message): void
    {
        $browser->assertPresent('[data-flash-type="success"]')
            ->assertSeeIn('[data-flash-type="success"]', $message);
    }

    /**
     * Assert error message is displayed.
     */
    public static function assertErrorMessage(Browser $browser, string $message): void
    {
        $browser->assertPresent('[data-flash-type="error"]')
            ->assertSeeIn('[data-flash-type="error"]', $message);
    }

    /**
     * Assert loading state is displayed.
     */
    public static function assertLoadingState(Browser $browser): void
    {
        $browser->assertPresent('[data-loading]')
            ->assertSeeIn('[data-loading]', 'Loading');
    }

    /**
     * Assert loading state is removed.
     */
    public static function assertLoadingComplete(Browser $browser): void
    {
        $browser->waitUntilMissing('[data-loading]', 10);
    }

    /**
     * Assert modal is displayed with correct content.
     */
    public static function assertModalDisplayed(Browser $browser, string $title, string $content = null): void
    {
        $browser->assertPresent('.modal.show')
            ->assertSeeIn('.modal-title', $title);

        if ($content) {
            $browser->assertSeeIn('.modal-body', $content);
        }
    }

    /**
     * Assert modal is closed.
     */
    public static function assertModalClosed(Browser $browser): void
    {
        $browser->assertDontSee('.modal.show')
            ->waitUntilMissing('.modal.show', 5);
    }

    /**
     * Assert table contains expected data.
     */
    public static function assertTableContains(Browser $browser, array $expectedData): void
    {
        foreach ($expectedData as $row) {
            $browser->assertSeeIn('table', $row);
        }
    }

    /**
     * Assert table does not contain specific data.
     */
    public static function assertTableDoesNotContain(Browser $browser, array $forbiddenData): void
    {
        foreach ($forbiddenData as $data) {
            $browser->assertDontSeeIn('table', $data);
        }
    }

    /**
     * Assert pagination works correctly.
     */
    public static function assertPaginationWorks(Browser $browser, int $totalItems, int $itemsPerPage): void
    {
        $expectedPages = ceil($totalItems / $itemsPerPage);
        
        if ($expectedPages > 1) {
            $browser->assertPresent('.pagination')
                ->assertSeeIn('.pagination', (string)$expectedPages);
        }
    }

    /**
     * Assert search functionality returns correct results.
     */
    public static function assertSearchResults(Browser $browser, string $searchTerm, array $expectedResults): void
    {
        $browser->type('search', $searchTerm)
            ->pause(500); // Wait for debounce

        foreach ($expectedResults as $result) {
            $browser->assertSeeIn('.search-results', $result);
        }
    }

    /**
     * assert export functionality generates file.
     */
    public static function assertExportGenerated(Browser $browser, string $exportType): void
    {
        $browser->click("[data-export='{$exportType}']")
            ->waitFor('.export-success', 10)
            ->assertSee('Export completed');
    }

    /**
     * Assert responsive design works on mobile.
     */
    public static function assertMobileResponsive(Browser $browser): void
    {
        $browser->resize(375, 667); // Mobile viewport
        $browser->assertPresent('.mobile-menu')
            ->assertMissing('.desktop-sidebar');
    }

    /**
     * Assert responsive design works on desktop.
     */
    public static function assertDesktopResponsive(Browser $browser): void
    {
        $browser->resize(1920, 1080); // Desktop viewport
        $browser->assertPresent('.desktop-sidebar')
            ->assertMissing('.mobile-menu');
    }

    /**
     * Assert dark mode toggle works.
     */
    public static function assertDarkModeToggle(Browser $browser): void
    {
        $browser->click('[data-theme-toggle]')
            ->assertPresent('body.dark');

        $browser->click('[data-theme-toggle]')
            ->assertMissing('body.dark');
    }

    /**
     * Assert accessibility features are present.
     */
    public static function assertAccessibilityFeatures(Browser $browser): void
    {
        $browser->assertPresent('[aria-label]')
            ->assertPresent('button[tabindex]')
            ->assertPresent('input[aria-describedby]');
    }

    /**
     * Assert performance metrics are within acceptable range.
     */
    public static function assertPerformanceMetrics(Browser $browser, array $metrics): void
    {
        foreach ($metrics as $metric => $maxValue) {
            $actualValue = $browser->script("return performance.{$metric};")[0];
            
            if ($actualValue > $maxValue) {
                throw new \Exception("Performance metric {$metric} ({$actualValue}) exceeds maximum ({$maxValue})");
            }
        }
    }

    /**
     * Assert real-time updates are working.
     */
    public static function assertRealTimeUpdate(Browser $browser, string $selector, callable $updateAction): void
    {
        $initialValue = $browser->text($selector);
        
        $updateAction();
        
        $browser->waitUntil(function ($browser) use ($selector, $initialValue) {
            return $browser->text($selector) !== $initialValue;
        }, 10);
    }

    /**
     * Assert audit trail entry exists.
     */
    public static function assertAuditTrailEntry(Browser $browser, string $action, string $user): void
    {
        $browser->clickLink('Audit Trail')
            ->waitFor('.audit-trail-table', 10)
            ->assertSeeIn('.audit-trail-table', $action)
            ->assertSeeIn('.audit-trail-table', $user);
    }

    /**
     * Assert notification is displayed.
     */
    public static function assertNotificationDisplayed(Browser $browser, string $message, string $type = 'info'): void
    {
        $browser->assertPresent("[data-notification-type='{$type}']")
            ->assertSeeIn("[data-notification-type='{$type}']", $message);
    }

    /**
     * Assert breadcrumb navigation is correct.
     */
    public static function assertBreadcrumb(Browser $browser, array $breadcrumbItems): void
    {
        $browser->assertPresent('nav[aria-label="breadcrumb"]');

        foreach ($breadcrumbItems as $item) {
            $browser->assertSeeIn('nav[aria-label="breadcrumb"]', $item);
        }
    }

    /**
     * Assert page title is correct.
     */
    public static function assertPageTitle(Browser $browser, string $expectedTitle): void
    {
        $browser->assertTitleContains($expectedTitle);
    }

    /**
     * Assert URL contains expected path.
     */
    public static function assertUrlContains(Browser $browser, string $expectedPath): void
    {
        $browser->assertPathContains($expectedPath);
    }
}