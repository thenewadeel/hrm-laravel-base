<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\ChartOfAccount;
use Livewire\Component;

class ChartOfAccounts extends Component
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $accounts;

    /**
     * Mount the component and fetch the chart of accounts.
     *
     * @return void
     */
    public function mount()
    {
        $this->accounts = ChartOfAccount::all();
    }

    /**
     * Render the Livewire component.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.accounting.chart-of-accounts');
    }
}
