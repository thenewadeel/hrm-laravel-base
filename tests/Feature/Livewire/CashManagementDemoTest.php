<?php

use App\Livewire\CashManagementDemo;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

it('renders the cash management demo component', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/demo/cash-management')
        ->assertStatus(200)
        ->assertSeeLivewire('cash-management-demo');
});

it('displays cash receipt and payment forms', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->assertSee('Create Cash Receipt')
        ->assertSee('Cash Receipt')
        ->assertSee('Cash Payment')
        ->assertSee('Received From')
        ->assertSee('Amount')
        ->assertSee('Date');
});

it('loads cash accounts for the organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    // Create cash accounts
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $otherAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
        'name' => 'Sales Revenue',
    ]);

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->assertSet('cashAccounts', function ($accounts) use ($cashAccount) {
            return $accounts->contains('id', $cashAccount->id);
        });
});

it('can switch between receipt and payment modes', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->assertSet('mode', 'receipt')
        ->call('setMode', 'payment')
        ->assertSet('mode', 'payment')
        ->assertSee('Create Cash Payment')
        ->assertDontSee('Create Cash Receipt');
});

it('validates receipt form data', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->set('receiptData.received_from', '')
        ->set('receiptData.amount', '')
        ->call('createReceipt')
        ->assertHasErrors([
            'receiptData.received_from' => 'required',
            'receiptData.amount' => 'required',
        ]);
});

it('validates payment form data', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->call('setMode', 'payment')
        ->set('paymentData.paid_to', '')
        ->set('paymentData.amount', '')
        ->call('createPayment')
        ->assertHasErrors([
            'paymentData.paid_to' => 'required',
            'paymentData.amount' => 'required',
        ]);
});

it('creates a cash receipt successfully', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $creditAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
        'name' => 'Revenue Account',
    ]);

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->set('receiptData.received_from', 'John Doe')
        ->set('receiptData.amount', 1000.00)
        ->set('receiptData.cash_account_id', $cashAccount->id)
        ->set('receiptData.credit_account_id', $creditAccount->id)
        ->set('receiptData.description', 'Test receipt')
        ->call('createReceipt')
        ->assertDispatched('cash-receipt-created')
        ->assertSee('Cash receipt created successfully');

    $this->assertDatabaseHas('cash_receipts', [
        'organization_id' => $organization->id,
        'received_from' => 'John Doe',
        'amount' => 1000.00,
        'description' => 'Test receipt',
    ]);
});

it('creates a cash payment successfully', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $debitAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
        'name' => 'Expense Account',
    ]);

    // Create a receipt first to give cash account balance
    $creditAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
        'name' => 'Revenue Account',
    ]);

    \App\Models\Accounting\CashReceipt::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
        'amount' => 1000.00,
    ]);

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->call('setMode', 'payment')
        ->set('paymentData.paid_to', 'Jane Smith')
        ->set('paymentData.amount', 500.00)
        ->set('paymentData.cash_account_id', $cashAccount->id)
        ->set('paymentData.debit_account_id', $debitAccount->id)
        ->set('paymentData.purpose', 'Test payment')
        ->call('createPayment')
        ->assertDispatched('cash-payment-created')
        ->assertSee('Cash payment created successfully');

    $this->assertDatabaseHas('cash_payments', [
        'organization_id' => $organization->id,
        'paid_to' => 'Jane Smith',
        'amount' => 500.00,
        'purpose' => 'Test payment',
    ]);
});

it('displays recent cash receipts and payments', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    // Create some test data
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
    ]);

    $creditAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
    ]);

    $debitAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
    ]);

    $receipt = \App\Models\Accounting\CashReceipt::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
    ]);

    $payment = \App\Models\Accounting\CashPayment::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
    ]);

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->assertSee($receipt->received_from)
        ->assertSee(number_format($receipt->amount, 2))
        ->assertSee($payment->paid_to)
        ->assertSee(number_format($payment->amount, 2));
});

it('resets form after successful receipt creation', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $creditAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
        'name' => 'Revenue Account',
    ]);

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->set('receiptData.received_from', 'John Doe')
        ->set('receiptData.amount', 1000.00)
        ->set('receiptData.cash_account_id', $cashAccount->id)
        ->set('receiptData.credit_account_id', $creditAccount->id)
        ->call('createReceipt')
        ->assertSet('receiptData.received_from', '')
        ->assertSet('receiptData.amount', '')
        ->assertSet('receiptData.cash_account_id', '')
        ->assertSet('receiptData.credit_account_id', '');
});

it('resets form after successful payment creation', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $debitAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
        'name' => 'Expense Account',
    ]);

    // Create a receipt first to give cash account balance
    $creditAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
        'name' => 'Revenue Account',
    ]);

    \App\Models\Accounting\CashReceipt::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
        'amount' => 1000.00,
    ]);

    Livewire::actingAs($user)
        ->test(CashManagementDemo::class, ['organizationId' => $organization->id])
        ->call('setMode', 'payment')
        ->set('paymentData.paid_to', 'Jane Smith')
        ->set('paymentData.amount', 500.00)
        ->set('paymentData.cash_account_id', $cashAccount->id)
        ->set('paymentData.debit_account_id', $debitAccount->id)
        ->call('createPayment')
        ->assertSet('paymentData.paid_to', '')
        ->assertSet('paymentData.amount', '')
        ->assertSet('paymentData.cash_account_id', '')
        ->assertSet('paymentData.debit_account_id', '');
});
