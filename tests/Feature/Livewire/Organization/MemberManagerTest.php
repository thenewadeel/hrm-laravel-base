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

class MemberManagerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_display_a_list_of_organization_members()
    {
        // Arrange
        $organization = Organization::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $organization->users()->attach($user1);
        $organization->users()->attach($user2);

        // Act & Assert
        Livewire::test(MemberManager::class, ['organization' => $organization])
            ->assertSee($user1->name)
            ->assertSee($user2->name);
    }

    #[Test]
    public function it_can_search_for_members_by_name_or_email()
    {
        // Arrange
        $organization = Organization::factory()->create();
        $user1 = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $user2 = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        $organization->users()->attach($user1);
        $organization->users()->attach($user2);

        // Act & Assert - Search for John Doe
        Livewire::test(MemberManager::class, ['organization' => $organization])
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');

        // Act & Assert - Search by email
        Livewire::test(MemberManager::class, ['organization' => $organization])
            ->set('search', 'jane@')
            ->assertSee('Jane Smith')
            ->assertDontSee('John Doe');
    }
}
