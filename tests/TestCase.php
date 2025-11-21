<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
// use Tests\Traits\SetupOrganization;
// use Tests\Traits\SetupInventory;

abstract class TestCase extends BaseTestCase
{

    // use SetupOrganization;
    // use SetupInventory;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     // Setup organization for tests that need it
    //     if (method_exists($this, 'setupOrganization')) {
    //         $this->setupOrganization();
    //     }
    // }
    // In tests/TestCase.php (or similar)
    // protected function tearDown(): void
    // {
    //     // ... (parent::tearDown() logic if any)

    //     // Force Garbage Collection
    //     gc_collect_cycles();

    //     parent::tearDown();
    // }
}
