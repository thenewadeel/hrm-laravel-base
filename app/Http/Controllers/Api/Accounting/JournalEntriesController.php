<?php
// app/Http/Controllers/Api/Accounting/JournalEntriesController.php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalEntry;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Http\Resources\JournalEntryResource;
use App\Services\AccountingService;

class JournalEntriesController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index()
    {
        $entries = JournalEntry::with(['ledgerEntries.account'])->latest()->get();

        return JournalEntryResource::collection($entries);
    }

    // app/Http/Controllers/Api/Accounting/JournalEntriesController.php

    public function store(StoreJournalEntryRequest $request)
    {
        $journalEntry = JournalEntry::create(
            $request->only(['entry_date', 'description']) +
                ['created_by' => auth()->id()]
        );

        // Process the ledger entries (to be implemented)
        // This will use the AccountingService to create balanced entries

        return new JournalEntryResource($journalEntry->load('ledgerEntries'));
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry)
    {
        abort_if($journalEntry->status !== 'draft', 422, 'Only draft entries can be updated');

        $journalEntry->update($request->validated());

        // Update ledger entries if provided
        if ($request->has('entries')) {
            // Delete existing entries and create new ones
            $journalEntry->ledgerEntries()->delete();
            // Process new entries using AccountingService
        }

        return new JournalEntryResource($journalEntry->load('ledgerEntries'));
    }

    public function show(JournalEntry $journalEntry)
    {
        return new JournalEntryResource($journalEntry->load('ledgerEntries.account'));
    }

    public function post(JournalEntry $journalEntry)
    {
        abort_if($journalEntry->status !== 'draft', 422, 'Only draft entries can be posted');

        $journalEntry->update([
            'status' => 'posted',
            'posted_at' => now(),
            'approved_by' => auth()->id()
        ]);

        return new JournalEntryResource($journalEntry);
    }

    public function void(JournalEntry $journalEntry)
    {
        abort_if($journalEntry->status !== 'posted', 422, 'Only posted entries can be voided');

        $journalEntry->void();

        return new JournalEntryResource($journalEntry);
    }
}
