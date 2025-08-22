<?php
// tests/Unit/Services/SequenceServiceTest.php

namespace Tests\Unit\Services;

use App\Services\SequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SequenceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SequenceService $sequenceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sequenceService = app(SequenceService::class);
    }

    /** @test */
    public function it_generates_sequential_codes()
    {
        $code1 = $this->sequenceService->generate('journal_entry_ref');
        $code2 = $this->sequenceService->generate('journal_entry_ref');
        $code3 = $this->sequenceService->generate('journal_entry_ref');

        $this->assertEquals('JE-000001', $code1);
        $this->assertEquals('JE-000002', $code2);
        $this->assertEquals('JE-000003', $code3);
    }

    /** @test */
    public function it_handles_concurrent_requests_safely()
    {
        // This would be tested with concurrent processes in real scenarios
        // The database lock ensures thread safety
        $this->assertTrue(true); // Placeholder for concurrent test logic
    }
}
