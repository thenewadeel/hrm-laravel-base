<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiTagBadges extends Component
{
    public array $tags;
    public int $limit;

    public function __construct(array $tags, int $limit = 10)
    {
        $this->tags = $tags;
        $this->limit = $limit;
    }

    public function getVisibleTags(): array
    {
        return array_slice($this->tags, 0, $this->limit);
    }

    public function getRemainingCount(): int
    {
        return max(0, count($this->tags) - $this->limit);
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-tag-badges');
    }
}