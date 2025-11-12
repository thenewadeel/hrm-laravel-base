<?php
// app/Models/Accounting/JournalEntry.php

namespace App\Models\Accounting;

use App\Exceptions\UnbalancedTransactionException;
use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\SequenceService;
use Database\Factories\Accounting\JournalEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JournalEntry extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'reference_number',
        'entry_date',
        'description',
        'status',
        'created_by',
        'approved_by',
        'posted_at'
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
    ];

    /**
     * Create a new journal entry within a database transaction.
     */
    public static function createWithTransaction(array $attributes = [])
    {
        Log::debug(json_encode($attributes) . " attributes");
        return DB::transaction(function () use ($attributes) {
            $attributes['created_by'] = Auth::id();
            $journalEntry = self::create($attributes);
            Log::debug(" journalEntry created");
            return $journalEntry;
        });
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (JournalEntry $journalEntry) {
            if (empty($journalEntry->reference_number)) {
                try {
                    $sequenceService = app(SequenceService::class);
                    $journalEntry->reference_number = $sequenceService->generate('journal_entry_ref');
                } catch (\Exception $e) {
                    // Fallback if sequence service fails
                    $latest = self::where('reference_number', 'like', 'JE-%')
                        ->orderBy('reference_number', 'desc')
                        ->first();

                    $nextNumber = $latest ? (int) str_replace('JE-', '', $latest->reference_number) + 1 : 1;
                    $journalEntry->reference_number = 'JE-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                }
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

    // public function organization(): BelongsTo
    // {
    //     return $this->belongsTo(Organization::class);
    // }

    /**
     * Fix the ledger entries relationship
     * Assuming LedgerEntry has a journal_entry_id foreign key
     */
    // public function ledgerEntries(): HasMany
    // {
    //     return $this->hasMany(LedgerEntry::class, 'journal_entry_id');
    //     }

    /**
     * Alternative if you're using polymorphic relationships:
     * Make sure the transactionable_type is stored correctly
     */
    /*
    */
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
