<?php
// tests/Feature/Api/Accounting/JournalEntriesApiTest.php

namespace Tests\Feature\Api\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class JournalEntriesApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_can_create_a_journal_entry()
    {
        $cashAccount = ChartOfAccount::factory()->create(['type' => 'asset']);
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue']);

        $entryData = [
            'entry_date' => now()->format('Y-m-d'),
            'description' => 'Test sale',
            'entries' => [
                ['account_id' => $cashAccount->id, 'type' => 'debit', 'amount' => 100.00],
                ['account_id' => $revenueAccount->id, 'type' => 'credit', 'amount' => 100.00],
            ]
        ];
        $this->actingAs($this->user);
        $response = $this->postJson('/api/journal-entries', $entryData);
        // dd($response->json());
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'reference_number',
                    'entry_date',
                    'description',
                    'status',
                    'created_by',
                    'approved_by',
                    'posted_at',
                    'created_at',
                    'updated_at',
                    // Remove 'ledger_entries' if you simplified further
                ]
            ])
            ->assertJson([
                'data' => [
                    'description' => 'Test sale',
                    'status' => 'draft' // Check for draft status
                ]
            ]);

        $this->assertDatabaseHas('journal_entries', [
            'description' => 'Test sale',
            'status' => 'draft'
        ]);
    }

    #[Test]
    public function it_validates_journal_entry_balances()
    {
        $account = ChartOfAccount::factory()->create();

        $entryData = [
            'entry_date' => now()->format('Y-m-d'),
            'description' => 'Unbalanced entry',
            'entries' => [
                ['account_id' => $account->id, 'type' => 'debit', 'amount' => 100.00],
                // Missing credit entry
            ]
        ];

        $this->actingAs($this->user);
        $response = $this->postJson('/api/journal-entries', $entryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['entries']);
    }

    #[Test]
    public function it_can_post_a_journal_entry()
    {
        $journalEntry = JournalEntry::factory()->draft()->create([
            'created_by' => $this->user->id
        ]);

        $this->actingAs($this->user);
        $response = $this->putJson("/api/journal-entries/{$journalEntry->id}/post");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'posted']);

        $this->assertDatabaseHas('journal_entries', [
            'id' => $journalEntry->id,
            'status' => 'posted',
            'posted_at' => now()
        ]);
    }
}
