<?php

namespace Tests\Browser\E2E\Concerns;

use Laravel\Dusk\Browser;

trait HandlesWorkflowAssertions
{
    /**
     * Assert financial data integrity across modules.
     */
    protected function assertFinancialDataIntegrity(Browser $browser, array $expectedData): void
    {
        // Check chart of accounts balance
        if (isset($expectedData['account_balances'])) {
            foreach ($expectedData['account_balances'] as $accountName => $expectedBalance) {
                $browser->assertSeeIn('[data-account-balance="' . $accountName . '"]', 
                    number_format($expectedBalance, 2));
            }
        }

        // Check journal entries
        if (isset($expectedData['journal_entries_count'])) {
            $browser->assertSeeIn('[data-journal-entries-count]', 
                $expectedData['journal_entries_count']);
        }

        // Check ledger entries balance
        if (isset($expectedData['ledger_balance'])) {
            $browser->assertSeeIn('[data-ledger-balance]', 
                number_format($expectedData['ledger_balance'], 2));
        }
    }

    /**
     * Assert employee data consistency.
     */
    protected function assertEmployeeDataConsistency(Browser $browser, array $expectedData): void
    {
        // Check employee count
        if (isset($expectedData['employee_count'])) {
            $browser->assertSeeIn('[data-employee-count]', $expectedData['employee_count']);
        }

        // Check payroll totals
        if (isset($expectedData['payroll_total'])) {
            $browser->assertSeeIn('[data-payroll-total]', 
                number_format($expectedData['payroll_total'], 2));
        }

        // Check attendance records
        if (isset($expectedData['attendance_records'])) {
            foreach ($expectedData['attendance_records'] as $employeeId => $records) {
                $browser->assertSeeIn('[data-attendance="' . $employeeId . '"]', $records);
            }
        }
    }

    /**
     * Assert inventory data accuracy.
     */
    protected function assertInventoryDataAccuracy(Browser $browser, array $expectedData): void
    {
        // Check stock levels
        if (isset($expectedData['stock_levels'])) {
            foreach ($expectedData['stock_levels'] as $itemName => $expectedLevel) {
                $browser->assertSeeIn('[data-stock-level="' . $itemName . '"]', $expectedLevel);
            }
        }

        // Check inventory value
        if (isset($expectedData['inventory_value'])) {
            $browser->assertSeeIn('[data-inventory-value]', 
                number_format($expectedData['inventory_value'], 2));
        }

        // Check transaction counts
        if (isset($expectedData['transaction_counts'])) {
            foreach ($expectedData['transaction_counts'] as $transactionType => $count) {
                $browser->assertSeeIn('[data-transaction-count="' . $transactionType . '"]', $count);
            }
        }
    }

    /**
     * Assert multi-tenant data isolation.
     */
    protected function assertDataIsolation(Browser $browser, Organization $organization): void
    {
        $browser->assertSee($organization->name)
            ->assertPresent('[data-organization-id="' . $organization->id . '"]')
            ->assertDontSeeAnyText([
                'Other Organization',
                'Unauthorized Data',
                'Access Denied'
            ]);
    }

    /**
     * Assert user role-based access control.
     */
    protected function assertRoleBasedAccess(Browser $browser, string $role, array $allowedActions, array $deniedActions): void
    {
        // Check allowed actions are visible
        foreach ($allowedActions as $action) {
            $browser->assertPresent('[data-action="' . $action . '"]')
                ->assertVisible('[data-action="' . $action . '"]');
        }

        // Check denied actions are not visible or disabled
        foreach ($deniedActions as $action) {
            $browser->assertMissing('[data-action="' . $action . '"]:not([disabled])');
        }
    }

    /**
     * Assert business process validation.
     */
    protected function assertBusinessProcessValidation(Browser $browser, array $processSteps): void
    {
        foreach ($processSteps as $step => $expectedStatus) {
            $browser->assertSeeIn('[data-process-step="' . $step . '"]', $expectedStatus);
        }
    }

    /**
     * Assert workflow completion status.
     */
    protected function assertWorkflowCompletion(Browser $browser, string $workflowName, bool $isCompleted = true): void
    {
        $status = $isCompleted ? 'completed' : 'pending';
        $browser->assertSeeIn('[data-workflow="' . $workflowName . '"]', $status);
    }

    /**
     * Assert error handling and recovery.
     */
    protected function assertErrorHandling(Browser $browser, string $errorScenario, callable $recoveryAction): void
    {
        // Trigger error scenario
        $this->triggerErrorScenario($browser, $errorScenario);

        // Assert error message is displayed
        $browser->assertPresent('[data-error-message]')
            ->assertSee('Error');

        // Perform recovery action
        $recoveryAction($browser);

        // Assert recovery is successful
        $browser->assertDontSee('Error')
            ->assertPresent('[data-recovery-success]');
    }

    /**
     * Trigger specific error scenario.
     */
    protected function triggerErrorScenario(Browser $browser, string $scenario): void
    {
        switch ($scenario) {
            case 'network_failure':
                $browser->script("navigator.offline = true;");
                break;
            case 'invalid_data':
                $browser->type('invalid_field', 'invalid_value')
                    ->click('button[type="submit"]');
                break;
            case 'permission_denied':
                $browser->visit('/restricted-page');
                break;
            case 'server_error':
                $browser->visit('/simulate-server-error');
                break;
        }
    }

    /**
     * Assert data persistence across page refreshes.
     */
    protected function assertDataPersistence(Browser $browser, array $dataToCheck): void
    {
        // Capture initial state
        $initialState = [];
        foreach ($dataToCheck as $selector => $expectedValue) {
            $initialState[$selector] = $browser->text($selector);
        }

        // Refresh page
        $browser->refresh();
        $this->waitForPageLoad($browser);

        // Verify data persistence
        foreach ($initialState as $selector => $value) {
            $browser->assertSeeIn($selector, $value);
        }
    }

    /**
     * Assert audit trail is maintained.
     */
    protected function assertAuditTrail(Browser $browser, array $expectedActions): void
    {
        $browser->clickLink('Audit Trail')
            ->waitFor('.audit-trail-table', 10);

        foreach ($expectedActions as $action) {
            $browser->assertSeeIn('.audit-trail-table', $action);
        }
    }

    /**
     * Assert report generation accuracy.
     */
    protected function assertReportAccuracy(Browser $browser, string $reportName, array $expectedData): void
    {
        $browser->clickLink($reportName)
            ->waitFor('.report-content', 15);

        foreach ($expectedData as $key => $value) {
            $browser->assertSeeIn('[data-report-field="' . $key . '"]', $value);
        }
    }

    /**
     * Assert notification system works correctly.
     */
    protected function assertNotificationSystem(Browser $browser, array $expectedNotifications): void
    {
        foreach ($expectedNotifications as $notification) {
            $browser->assertSeeIn('[data-notification]', $notification['message'])
                ->assertSeeIn('[data-notification-type]', $notification['type']);
        }
    }

    /**
     * Assert search and filtering functionality.
     */
    protected function assertSearchAndFiltering(Browser $browser, array $searchTests): void
    {
        foreach ($searchTests as $test) {
            $browser->type('search', $test['query'])
                ->pause(500) // Wait for debounce
                ->assertSeeIn('.search-results', $test['expected_result']);

            if (isset($test['should_not_contain'])) {
                $browser->assertDontSeeIn('.search-results', $test['should_not_contain']);
            }
        }
    }

    /**
     * Assert export functionality works.
     */
    protected function assertExportFunctionality(Browser $browser, string $exportType): void
    {
        $browser->click('[data-export="' . $exportType . '"]')
            ->waitFor('.export-success', 10)
            ->assertSee('Export completed successfully');
    }

    /**
     * Assert batch operations work correctly.
     */
    protected function assertBatchOperations(Browser $browser, array $operations): void
    {
        foreach ($operations as $operation) {
            // Select items for batch operation
            foreach ($operation['items'] as $item) {
                $browser->check('[data-batch-item="' . $item . '"]');
            }

            // Execute batch operation
            $browser->click('[data-batch-action="' . $operation['action'] . '"]')
                ->waitFor('.batch-result', 10)
                ->assertSee($operation['expected_result']);
        }
    }

    /**
     * Assert real-time updates work.
     */
    protected function assertRealTimeUpdates(Browser $browser, callable $updateAction): void
    {
        // Get initial state
        $initialValue = $browser->text('[data-real-time-field]');

        // Perform update action in another context
        $updateAction();

        // Wait for real-time update
        $browser->waitUntil(function ($browser) use ($initialValue) {
            return $browser->text('[data-real-time-field]') !== $initialValue;
        }, 10);

        // Assert value has changed
        $newValue = $browser->text('[data-real-time-field]');
        $this->assertNotEquals($initialValue, $newValue);
    }
}