<?php

namespace App\Livewire\Accounting\FinancialYears;

use App\Models\Accounting\FinancialYear;
use App\Services\FinancialYearService;
use Livewire\Component;

class FinancialYearForm extends Component
{
    public FinancialYear $financialYear;

    public $name;

    public $code;

    public $start_date;

    public $end_date;

    public $notes;

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'end_date.after' => 'The end date must be after the start date.',
    ];

    public function mount(?FinancialYear $financialYear = null)
    {
        $this->financialYear = $financialYear ?? new FinancialYear;
        $this->fill($this->financialYear->only(['name', 'code', 'start_date', 'end_date', 'notes']));
    }

    public function save()
    {
        $this->validate();

        $data = [
            'organization_id' => auth()->user()->current_organization_id,
            'name' => $this->name,
            'code' => $this->code,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'notes' => $this->notes,
        ];

        $financialYearService = app(FinancialYearService::class);

        if ($this->financialYear->exists) {
            $this->financialYear->update($data);
            $message = 'Financial year updated successfully.';
        } else {
            $this->financialYear = $financialYearService->createFinancialYear($data);
            $message = 'Financial year created successfully.';
        }

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => $message,
        ]);

        return redirect()->route('accounting.financial-years.index');
    }

    public function render()
    {
        return view('livewire.accounting.financial-years.financial-year-form');
    }
}
