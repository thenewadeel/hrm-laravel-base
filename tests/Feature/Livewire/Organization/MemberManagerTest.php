<?php

namespace Tests\Feature\Livewire\Organization;

use App\Livewire\MemberManager;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupOrganization;

class MemberManagerTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
    }
    #[Test]
    public function it_can_display_a_list_of_organization_members()
    {
        // Arrange
        $organization = $this->organization; // Organization::factory()->create();
        $user1 = $this->user;
        $user2 = User::factory()->create();
        // $organization->users()->attach($user1);
        $organization->users()->attach($user2, [
            'roles' => json_encode(["admin"]),
            'organization_id' => $organization->id
        ]);
        // Act & Assert
        Livewire::test(MemberManager::class, ['organization' => $organization])
            ->assertSee($user1->name)
            ->assertSee($user2->name);
    }

    #[Test]
    public function it_can_search_for_members_by_name_or_email()
    {
        // Arrange
        $organization = $this->organization; // Organization::factory()->create();
        $user1 = $this->user;
        $user2 = User::factory()->create();
        // $organization->users()->attach($user1);
        $organization->users()->attach($user2, [
            'roles' => json_encode(["admin"]),
            'organization_id' => $organization->id
        ]);

        // Act & Assert - Search for John Doe
        Livewire::test(MemberManager::class, ['organization' => $organization])
            ->set('search', $user1->name)
            ->assertSee($user1->name)
            ->assertDontSee($user2->name);

        // Act & Assert - Search by email
        Livewire::test(MemberManager::class, ['organization' => $organization])
            ->set('search', $user2->name)
            ->assertSee($user2->name)
            ->assertDontSee($user1->name);
    }
}
