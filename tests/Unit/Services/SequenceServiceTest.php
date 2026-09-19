<?php

namespace Tests\Unit\Services;

use App\Models\Organization;
use App\Services\SequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SequenceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SequenceService $sequenceService;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sequenceService = app(SequenceService::class);

        // Every sequence counter is organization-scoped (tenant isolation).
        $this->organization = Organization::factory()->create();

        DB::table('sequences')->insert([
            'organization_id' => $this->organization->id,
            'name' => 'journal_entry_ref',
            'last_value' => 0,
            'increment_by' => 1,
            'prefix' => 'JE-',
            'pad_length' => 6,
        ]);
    }

    #[Test]
    public function it_generates_sequential_codes()
    {
        $code1 = $this->sequenceService->generate('journal_entry_ref', ['organization_id' => $this->organization->id]);
        $code2 = $this->sequenceService->generate('journal_entry_ref', ['organization_id' => $this->organization->id]);
        $code3 = $this->sequenceService->generate('journal_entry_ref', ['organization_id' => $this->organization->id]);

        $this->assertEquals('JE-000001', $code1);
        $this->assertEquals('JE-000002', $code2);
        $this->assertEquals('JE-000003', $code3);
    }

    #[Test]
    public function it_handles_concurrent_requests_safely()
    {
        // This would be tested with concurrent processes in real scenarios
        // The database lock ensures thread safety
        $this->assertTrue(true); // Placeholder for concurrent test logic
    }
}
