<?php
// app/Models/Accounting/JournalEntry.php

namespace App\Models\Accounting;

use App\Exceptions\UnbalancedTransactionException;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\SequenceService;
use Database\Factories\Accounting\JournalEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class JournalEntry extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference_number',
        'entry_date',
        'description',
        'status',
        'created_by',
        'approved_by',
        'posted_at'
    ];

    protected $attributes = [
        'status' => 'draft', // ← Default value
    ];
    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
    ];
    public static function createWithTransaction(array $attributes = [])
    {
        return DB::transaction(function () use ($attributes) {
            $sequenceService = app(SequenceService::class);

            // Reserve a sequence number first
            $reservation = $sequenceService->reserve('journal_entry_ref');

            try {
                $journalEntry = self::create(array_merge($attributes, [
                    'reference_number' => $reservation['formatted']
                ]));

                // Commit the sequence reservation
                $sequenceService->commitReservation('journal_entry_ref', $reservation['value']);

                return $journalEntry;
            } catch (\Exception $e) {
                // The sequence reservation will automatically roll back with the transaction
                // No need to manually revert since we never committed it
                throw $e;
            }
        });
    }

    // Keep the old behavior for non-critical cases
    protected static function booted(): void
    {
        static::creating(function (JournalEntry $journalEntry) {
            if (empty($journalEntry->reference_number)) {
                $sequenceService = app(SequenceService::class);
                $journalEntry->reference_number = $sequenceService->generate('journal_entry_ref');
            }
        });
    }
    protected static function newFactory(): JournalEntryFactory
    {
        return JournalEntryFactory::new();
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'transactionable_id')
            ->where('transactionable_type', self::class);
    }

    /**
     * Post the journal entry to the general ledger
     */
    public function post(array $entries): void
    {
        $accountingService = app(AccountingService::class);

        $accountingService->postTransaction($entries, $this->description, $this);

        $this->update([
            'status' => 'posted',
            'posted_at' => now(),
        ]);
    }

    /**
     * Void a posted journal entry (create reversing entries)
     */
    public function void(): void
    {
        if ($this->status !== 'posted') {
            throw new \LogicException('Only posted journal entries can be voided');
        }

        $reversingEntries = $this->ledgerEntries->map(function ($entry) {
            return [
                'account' => $entry->account,
                'type' => $entry->type === 'debit' ? 'credit' : 'debit',
                'amount' => $entry->amount,
            ];
        })->toArray();

        $reversingJournal = JournalEntry::create([
            'reference_number' => $this->reference_number . '-VOID',
            'entry_date' => now(),
            'description' => 'Reversal of: ' . $this->description,
            'status' => 'draft',
            'created_by' => $this->created_by,
        ]);

        $reversingJournal->post($reversingEntries);

        $this->update(['status' => 'void']);
    }
}
