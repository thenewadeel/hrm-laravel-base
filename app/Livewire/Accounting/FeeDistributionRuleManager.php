<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FeeDistributionRule;
use App\Models\Accounting\FeeDistributionRuleItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FeeDistributionRuleManager extends Component
{
    use WithPagination;

    public $search = '';

    public $rules = [];

    public $chartOfAccounts = [];

    public $showCreateModal = false;

    public $showEditModal = false;

    public $showItemsModal = false;

    public $selectedRule = null;

    public $editingRule = null;

    public $ruleItems = [];

    // Form fields
    public $name = '';

    public $fee_type = '';

    public $rule_type = 'percentage';

    public $conditions = [];

    public $is_active = true;

    public $priority = 0;

    public $description = '';

    // Item form fields
    public $item_chart_of_account_id = '';

    public $item_distribution_type = 'percentage';

    public $item_percentage = 0;

    public $item_fixed_amount = 0;

    public $item_priority = 0;

    public $item_description = '';

    protected $validationRules = [
        'name' => 'required|string|max:255',
        'fee_type' => 'required|string|max:255',
        'rule_type' => 'required|in:percentage,fixed,priority',
        'priority' => 'integer|min:0',
        'description' => 'nullable|string',
    ];

    protected $itemRules = [
        'item_chart_of_account_id' => 'required|exists:chart_of_accounts,id',
        'item_distribution_type' => 'required|in:percentage,fixed',
        'item_percentage' => 'required_if:item_distribution_type,percentage|numeric|min:0|max:100',
        'item_fixed_amount' => 'required_if:item_distribution_type,fixed|numeric|min:0',
        'item_priority' => 'integer|min:0',
        'item_description' => 'nullable|string',
    ];

    public function mount()
    {
        if (! Auth::check()) {
            abort(401);
        }

        $this->loadChartOfAccounts();
        $this->loadRules();
    }

    public function loadChartOfAccounts()
    {
        if (! Auth::check()) {
            $this->chartOfAccounts = [];

            return;
        }

        $this->chartOfAccounts = ChartOfAccount::where('organization_id', Auth::user()->current_organization_id)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(function ($account) {
                return [$account->id => "{$account->code} - {$account->name} ({$account->type})"];
            })
            ->toArray();
    }

    public function loadRules()
    {
        if (! Auth::check()) {
            $this->rules = [];

            return;
        }

        $this->rules = FeeDistributionRule::where('organization_id', Auth::user()->current_organization_id)
            ->with(['items.chartOfAccount'])
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    public function createRule()
    {
        if (! Auth::check()) {
            abort(401);
        }

        $this->validate($this->validationRules);

        $rule = FeeDistributionRule::create([
            'organization_id' => Auth::user()->current_organization_id,
            'name' => $this->name,
            'fee_type' => $this->fee_type,
            'rule_type' => $this->rule_type,
            'conditions' => $this->conditions,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
            'description' => $this->description,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;
        $this->loadRules();

        $this->dispatch('rule-created', "Rule '{$rule->name}' created successfully.");
    }

    public function editRule(FeeDistributionRule $rule)
    {
        $this->editingRule = $rule;
        $this->name = $rule->name;
        $this->fee_type = $rule->fee_type;
        $this->rule_type = $rule->rule_type;
        $this->conditions = $rule->conditions ?? [];
        $this->is_active = $rule->is_active;
        $this->priority = $rule->priority;
        $this->description = $rule->description;
        $this->showEditModal = true;
    }

    public function updateRule()
    {
        $this->validate($this->validationRules);

        $this->editingRule->update([
            'name' => $this->name,
            'fee_type' => $this->fee_type,
            'rule_type' => $this->rule_type,
            'conditions' => $this->conditions,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
            'description' => $this->description,
        ]);

        $updatedName = $this->name; // Use the updated name from form

        $this->resetForm();
        $this->showEditModal = false;
        $this->loadRules();

        $this->dispatch('rule-updated', "Rule '{$updatedName}' updated successfully.");
    }

    public function deleteRule(FeeDistributionRule $rule)
    {
        $rule->delete();
        $this->loadRules();

        $this->dispatch('rule-deleted', "Rule '{$rule->name}' deleted successfully.");
    }

    public function manageItems(FeeDistributionRule $rule)
    {
        $this->selectedRule = $rule;
        $this->ruleItems = $rule->items()->with('chartOfAccount')->get()->toArray();
        $this->showItemsModal = true;
    }

    public function addItem()
    {
        $this->validate($this->itemRules);

        FeeDistributionRuleItem::create([
            'fee_distribution_rule_id' => $this->selectedRule->id,
            'chart_of_account_id' => $this->item_chart_of_account_id,
            'distribution_type' => $this->item_distribution_type,
            'percentage' => $this->item_percentage,
            'fixed_amount' => $this->item_fixed_amount,
            'priority' => $this->item_priority,
            'description' => $this->item_description,
        ]);

        $this->resetItemForm();
        $this->refreshRuleItems();

        $this->dispatch('item-added', 'Distribution item added successfully.');
    }

    public function deleteItem($itemId)
    {
        $item = FeeDistributionRuleItem::find($itemId);
        if ($item) {
            $item->delete();
            $this->refreshRuleItems();
            $this->dispatch('item-deleted', 'Distribution item deleted successfully.');
        }
    }

    public function refreshRuleItems()
    {
        $this->ruleItems = $this->selectedRule->items()
            ->with('chartOfAccount')
            ->orderBy('priority')
            ->get()
            ->toArray();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->fee_type = '';
        $this->rule_type = 'percentage';
        $this->conditions = [];
        $this->is_active = true;
        $this->priority = 0;
        $this->description = '';
        $this->editingRule = null;
    }

    public function resetItemForm()
    {
        $this->item_chart_of_account_id = '';
        $this->item_distribution_type = 'percentage';
        $this->item_percentage = 0;
        $this->item_fixed_amount = 0;
        $this->item_priority = 0;
        $this->item_description = '';
    }

    public function getFeeTypesProperty()
    {
        return [
            'subscription' => 'Subscription Fee',
            'late_fee' => 'Late Fee',
            'penalty' => 'Penalty',
            'registration' => 'Registration Fee',
            'renewal' => 'Renewal Fee',
            'other' => 'Other Fee',
        ];
    }

    public function getRuleTypesProperty()
    {
        return [
            'percentage' => 'Percentage-based',
            'fixed' => 'Fixed Amount',
            'priority' => 'Priority-based',
        ];
    }

    public function render()
    {
        if (! Auth::check()) {
            abort(401);
        }

        return view('livewire.accounting.fee-distribution-rule-manager');
    }
}
