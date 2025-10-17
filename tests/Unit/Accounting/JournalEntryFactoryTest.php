<?php
// tests/Unit/Accounting/JournalEntryFactoryTest.php

namespace Tests\Unit\Accounting;

use App\Models\Accounting\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalEntryFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_journal_entry()
    {
        $journalEntry = JournalEntry::factory()->create();

        $this->assertNotNull($journalEntry);
        $this->assertMatchesRegularExpression('/^JE-\d{4}$/', $journalEntry->reference_number);
        $this->assertNotNull($journalEntry->created_by);
    }

    /** @test */
    public function it_can_create_a_draft_journal_entry()
    {
        $journalEntry = JournalEntry::factory()->draft()->create();

        $this->assertEquals('draft', $journalEntry->status);
        $this->assertNull($journalEntry->posted_at);
    }

    /** @test */
    public function it_can_create_a_posted_journal_entry()
    {
        $journalEntry = JournalEntry::factory()->posted()->create();

        $this->assertEquals('posted', $journalEntry->status);
        $this->assertNotNull($journalEntry->posted_at);
    }

    /** @test */
    public function it_can_create_journal_entry_with_specific_approver()
    {
        $journalEntry = JournalEntry::factory()
            ->approvedBy(\App\Models\User::factory()->create())
            ->create();

        $this->assertNotNull($journalEntry->approved_by);
    }
}
