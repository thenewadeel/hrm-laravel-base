<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModelSelectDropdown extends Component
{
    public string $name;

    public string $label;

    public $value;

    public array $options;

    public string $placeholder;

    public bool $searchable;

    public bool $multiple;

    public string $size;

    public bool $required;

    public string $searchPlaceholder;

    public int $maxVisible;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        string $label,
        $value = null,
        array $options = [],
        string $placeholder = 'Select an option',
        bool $searchable = true,
        bool $multiple = false,
        string $size = 'md',
        bool $required = false,
        string $searchPlaceholder = 'Search...',
        int $maxVisible = 10
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
        $this->options = $options;
        $this->placeholder = $placeholder;
        $this->searchable = $searchable;
        $this->multiple = $multiple;
        $this->size = $size;
        $this->required = $required;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->maxVisible = $maxVisible;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.model-select-dropdown');
    }
}
