<?php
// tests/Feature/Api/Accounting/ChartOfAccountsApiTest.php

namespace Tests\Feature\Api\Accounting;


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Accounting\ChartOfAccount;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupOrganization;

class ChartOfAccountsApiTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;

    // protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->actingAs($this->user);
        // dd($this->user->organizations);
        // $this->user = User::factory()->create();
    }


    #[Test]
    public function it_can_list_chart_of_accounts()
    {
        $user = $this->user; //auth()->user();
        // dd([
        //     $user->id,
        //     $user->current_organization_id,
        //     $user->organizations,
        //     $user->getAllRoles(),
        //     $user->getAllPermissions(),
        //     // $user->hasPermission(OrganizationPermissions::CREATE_ORGANIZATION),
        //     // $user->hasRole(OrganizationRoles::SUPER_ADMIN)
        // ]);
        $accounts = ChartOfAccount::factory()->count(3)->create();

        $response = $this->getJson('/api/accounts');
        // dd($response->json());
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name', 'type', 'description', 'balance']
                ]
            ]);
    }

    #[Test]
    public function it_can_create_a_chart_of_account()
    {
        $accountData = [
            'code' => '9999',
            'name' => 'Test Account',
            'type' => 'expense',
            'description' => 'Test account description',
            'organization_id' => $this->organization->id
        ];

        // $this->actingAs($this->user);
        $response = $this->postJson('/api/accounts', $accountData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => $accountData // Check inside the 'data' key
            ]);

        $this->assertDatabaseHas('chart_of_accounts', $accountData);
    }

    #[Test]
    public function it_validates_account_creation()
    {
        // $this->actingAs($this->user);
        $response = $this->postJson('/api/accounts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'type']);
    }

    #[Test]
    public function it_can_show_a_specific_account()
    {
        $account = ChartOfAccount::factory()->create();

        // $this->actingAs($this->user);
        $response = $this->getJson("/api/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [ // Add this 'data' wrapper
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name
                ]
            ]);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_account()
    {
        // $this->actingAs($this->user);
        $response = $this->getJson('/api/accounts/9999');

        $response->assertStatus(404);
    }
}
