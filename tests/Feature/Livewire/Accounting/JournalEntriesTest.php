<?php

namespace Tests\Feature\Livewire\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SetupOrganization;

class JournalEntriesTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
    }

    public function test_it_renders_successfully()
    {
        Livewire::test('accounting.journal-entries')
            ->assertStatus(200);
    }

    public function test_it_creates_a_journal_entry()
    {
        $this->actingAs($this->user);
        $account1 = ChartOfAccount::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $account2 = ChartOfAccount::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $component = Livewire::test('accounting.journal-entries')
            ->set('entry_date', '2025-01-01')
            ->set('description', 'Test entry')
            ->set('transactions.0.account_id', $account1->id)
            ->set('transactions.0.debit', 500)
            ->set('transactions.0.credit', 0)
            ->set('transactions.1.account_id', $account2->id)
            ->set('transactions.1.debit', 0)
            ->set('transactions.1.credit', 500);

        $component->call('createEntry');

        $entry = JournalEntry::where('description', 'Test entry')->first();
        $this->assertNotNull($entry);
        $this->assertEquals('2025-01-01', $entry->entry_date->format('Y-m-d'));
        $this->assertEquals('posted', $entry->status);

        $entry = JournalEntry::where('description', 'Test entry')->first();

        $this->assertCount(2, $entry->ledgerEntries);

        $this->assertDatabaseHas('ledger_entries', [
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $entry->id,
            'chart_of_account_id' => $account1->id,
            'type' => 'debit',
            'amount' => 500.00,
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $entry->id,
            'chart_of_account_id' => $account2->id,
            'type' => 'credit',
            'amount' => 500.00,
        ]);

        $component->assertDispatched('notify', [
            'type' => 'success',
            'message' => 'Journal Entry created and posted successfully.',
        ]);
    }

    public function test_a_journal_entry_requires_balanced_debits_and_credits()
    {
        $this->actingAs($this->user);
        $account1 = ChartOfAccount::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $account2 = ChartOfAccount::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $component = Livewire::test('accounting.journal-entries')
            ->set('entry_date', '2025-01-01')
            ->set('description', 'Unbalanced entry')
            ->set('transactions.0.account_id', $account1->id)
            ->set('transactions.0.debit', 500)
            ->set('transactions.0.credit', 0)
            ->set('transactions.1.account_id', $account2->id)
            ->set('transactions.1.debit', 0)
            ->set('transactions.1.credit', 499);

        $component->call('createEntry')
            ->assertHasErrors(['transactions' => 'Total debits must equal total credits.']);

        $this->assertDatabaseMissing('journal_entries', [
            'description' => 'Unbalanced entry',
        ]);
    }
}
