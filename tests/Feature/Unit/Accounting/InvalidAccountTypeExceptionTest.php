<?php
// tests/Unit/Accounting/InvalidAccountTypeExceptionTest.php

namespace Tests\Unit\Accounting;

use App\Exceptions\InvalidAccountTypeException;
use App\Models\Accounting\ChartOfAccount;
use Tests\TestCase;

class InvalidAccountTypeExceptionTest extends TestCase
{
    /** @test */
    public function it_provides_detailed_error_message()
    {
        $account = ChartOfAccount::factory()->make(['type' => 'liability', 'code' => '2010']);

        $exception = new InvalidAccountTypeException($account, 'debit');

        $this->assertStringContainsString('Cannot debit a liability account', $exception->getMessage());
        $this->assertStringContainsString('Code: 2010', $exception->getMessage());
        $this->assertStringContainsString('asset, expense', $exception->getMessage());
    }

    /** @test */
    public function it_provides_contextual_information()
    {
        $account = ChartOfAccount::factory()->make(['type' => 'asset', 'code' => '1010']);

        $exception = new InvalidAccountTypeException($account, 'credit');

        $context = $exception->context();

        $this->assertEquals('1010', $context['account_code']);
        $this->assertEquals('asset', $context['account_type']);
        $this->assertEquals('credit', $context['attempted_entry_type']);
        $this->assertEquals(['liability', 'equity', 'revenue'], $context['valid_account_types']);
    }

    /** @test */
    public function it_allows_custom_message()
    {
        $account = ChartOfAccount::factory()->make(['type' => 'revenue']);
        $customMessage = 'Custom error message';

        $exception = new InvalidAccountTypeException($account, 'debit', $customMessage);

        $this->assertEquals($customMessage, $exception->getMessage());
    }
}
