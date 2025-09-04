<?php

namespace App\Livewire\Accounting;

use Livewire\Component;

class Dashboard extends Component
{
    // Placeholder data for the dashboard.
    // In a real application, you would fetch this from your database
    // using your accounting services.
    public $summary = [
        'total_revenue' => 55000,
        'total_expenses' => 32000,
        'net_income' => 23000,
        'cash_on_hand' => 15000,
        'accounts_receivable' => 12000,
        'accounts_payable' => 8500,
    ];

    public function render()
    {
        return view('livewire.accounting.dashboard');
    }
}
