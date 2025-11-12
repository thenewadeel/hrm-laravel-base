<?php
// tests/Unit/Accounting/JournalEntryTest.php

namespace Tests\Unit\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupOrganization;

class JournalEntryTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
    }
    #[Test]
    public function it_can_create_a_journal_entry()
    {
        $user = User::factory()->create();

        $journalEntry = JournalEntry::create([
            'reference_number' => 'JE-001',
            'entry_date' => now(),
            'description' => 'Test journal entry',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->assertNotNull($journalEntry);
        $this->assertEquals('draft', $journalEntry->status);
    }

    #[Test]
    public function it_can_post_a_balanced_journal_entry()
    {
        $user = User::factory()->create();
        $cashAccount = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1010']);
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue', 'code' => '4010']);

        $journalEntry = JournalEntry::create([
            'reference_number' => 'JE-002',
            'entry_date' => now(),
            'description' => 'Cash sale',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        // This should not throw an exception
        $journalEntry->post([
            ['account' => $cashAccount, 'type' => 'debit', 'amount' => 100.00],
            ['account' => $revenueAccount, 'type' => 'credit', 'amount' => 100.00],
        ]);

        $this->assertEquals('posted', $journalEntry->fresh()->status);
        $this->assertNotNull($journalEntry->posted_at);
    }

    #[Test]
    public function it_prevents_posting_unbalanced_journal_entries()
    {
        $user = User::factory()->create();
        $cashAccount = ChartOfAccount::factory()->create(['type' => 'asset']);

        $journalEntry = JournalEntry::create([
            'reference_number' => 'JE-003',
            'entry_date' => now(),
            'description' => 'Unbalanced entry',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->expectException(\App\Exceptions\UnbalancedTransactionException::class);

        $journalEntry->post([
            ['account' => $cashAccount, 'type' => 'debit', 'amount' => 100.00],
            // Missing credit entry
        ]);
    }
}
