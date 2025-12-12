<?php

namespace App\Livewire\Membership;

use Livewire\Component;

class FeeStructureSettings extends Component
{
    public array $feeRules = [
        'annual_subscription' => [
            'name' => 'Annual Subscription',
            'amount' => 500000,
            'description' => 'Yearly membership fee for all members',
            'billing_cycle' => 'yearly',
            'due_date' => 'April 1st',
            'editable' => true,
            'active' => true,
        ],
        'monthly_subscription' => [
            'name' => 'Monthly Subscription',
            'amount' => 50000,
            'description' => 'Monthly membership fee',
            'billing_cycle' => 'monthly',
            'due_date' => '1st of each month',
            'editable' => true,
            'active' => true,
        ],
        'sports_facilities' => [
            'name' => 'Sports Facilities',
            'amount' => 25000,
            'description' => 'Access to sports facilities and equipment',
            'billing_cycle' => 'monthly',
            'due_date' => '1st of each month',
            'editable' => true,
            'active' => true,
        ],
    ];

    public array $specialRules = [
        'late_fee' => [
            'name' => 'Late Fee Fine',
            'condition' => 'Implements after April',
            'percentage' => 10,
            'description' => '10% additional charge on overdue payments after April 1st',
            'type' => 'penalty',
            'active' => true,
        ],
        'senior_discount' => [
            'name' => 'Senior Citizen Discount',
            'condition' => 'Members over 70 years',
            'percentage' => 25,
            'description' => '25% discount on all fees for members aged 70 and above',
            'type' => 'discount',
            'active' => true,
        ],
    ];

    public bool $showEditModal = false;

    public string $editingRule = '';

    public array $editingData = [];

    public bool $showAddRuleModal = false;

    public array $newRule = [
        'name' => '',
        'amount' => 0,
        'description' => '',
        'billing_cycle' => 'monthly',
        'due_date' => '1st of each month',
        'active' => true,
    ];

    public function editRule(string $ruleKey): void
    {
        $this->editingRule = $ruleKey;

        if (isset($this->feeRules[$ruleKey])) {
            $this->editingData = $this->feeRules[$ruleKey];
        } elseif (isset($this->specialRules[$ruleKey])) {
            $this->editingData = $this->specialRules[$ruleKey];
        }

        $this->showEditModal = true;
    }

    public function updateRule(): void
    {
        if (isset($this->feeRules[$this->editingRule])) {
            $this->feeRules[$this->editingRule] = $this->editingData;
        } elseif (isset($this->specialRules[$this->editingRule])) {
            $this->specialRules[$this->editingRule] = $this->editingData;
        }

        $this->closeEditModal();

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Rule updated successfully',
        ]);
    }

    public function toggleRuleStatus(string $ruleKey): void
    {
        if (isset($this->feeRules[$ruleKey])) {
            $this->feeRules[$ruleKey]['active'] = ! $this->feeRules[$ruleKey]['active'];
        } elseif (isset($this->specialRules[$ruleKey])) {
            $this->specialRules[$ruleKey]['active'] = ! $this->specialRules[$ruleKey]['active'];
        }

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Rule status updated',
        ]);
    }

    public function deleteRule(string $ruleKey): void
    {
        if (isset($this->feeRules[$ruleKey])) {
            unset($this->feeRules[$ruleKey]);
        } elseif (isset($this->specialRules[$ruleKey])) {
            unset($this->specialRules[$ruleKey]);
        }

        $this->dispatch('show-notification', [
            'type' => 'warning',
            'message' => 'Rule deleted successfully',
        ]);
    }

    public function showAddNewRule(): void
    {
        $this->newRule = [
            'name' => '',
            'amount' => 0,
            'description' => '',
            'billing_cycle' => 'monthly',
            'due_date' => '1st of each month',
            'active' => true,
        ];
        $this->showAddRuleModal = true;
    }

    public function addNewRule(): void
    {
        $ruleKey = strtolower(str_replace(' ', '_', $this->newRule['name']));
        $this->feeRules[$ruleKey] = array_merge($this->newRule, [
            'editable' => true,
        ]);

        $this->closeAddRuleModal();

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'New rule added successfully',
        ]);
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingRule = '';
        $this->editingData = [];
    }

    public function closeAddRuleModal(): void
    {
        $this->showAddRuleModal = false;
        $this->newRule = [
            'name' => '',
            'amount' => 0,
            'description' => '',
            'billing_cycle' => 'monthly',
            'due_date' => '1st of each month',
            'active' => true,
        ];
    }

    public function getTotalAnnualRevenue(): float
    {
        $total = 0;
        foreach ($this->feeRules as $rule) {
            if ($rule['active']) {
                $amount = $rule['amount'];
                if ($rule['billing_cycle'] === 'monthly') {
                    $amount *= 12;
                }
                $total += $amount;
            }
        }

        return $total;
    }

    public function getActiveRulesCount(): int
    {
        $count = 0;
        foreach ($this->feeRules as $rule) {
            if ($rule['active']) {
                $count++;
            }
        }
        foreach ($this->specialRules as $rule) {
            if ($rule['active']) {
                $count++;
            }
        }

        return $count;
    }

    public function render()
    {
        return view('livewire.membership.fee-structure-settings');
    }
}
