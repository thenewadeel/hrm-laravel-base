<?php

namespace App\Livewire\Dashboard;

use App\Models\Organization;
use App\Models\UserDashboardPreference;
use App\Services\Dashboard\ExecutiveOverviewService;
use Livewire\Component;

/**
 * The Executive (Eagle Eye) dashboard: an organization-wide command center that
 * renders D3-powered SVG widgets and persists each user's custom layout.
 */
class ExecutiveDashboard extends Component
{
    /**
     * Hero KPI descriptors.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $kpis = [];

    /**
     * Widget registry keyed by widget key.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $widgets = [];

    /**
     * Ordered list of visible widget keys (personalized layout).
     *
     * @var array<int, string>
     */
    public array $layout = [];

    /**
     * The unified operations feed.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $activity = [];

    /**
     * Signals the UI that the last layout change was persisted.
     */
    public bool $saved = false;

    public ?string $organizationName = null;

    public int $organizationId = 0;

    /**
     * @var array<int, string> All widget keys available for this build.
     */
    public array $available = [];

    public function mount(Organization $organization): void
    {
        $this->organizationId = $organization->id;
        $this->organizationName = $organization->name;
        $this->loadData($organization);
    }

    /**
     * Recompute all aggregates and resolve the personalized layout.
     */
    protected function loadData(Organization $organization): void
    {
        $payload = app(ExecutiveOverviewService::class)->build($organization);

        $this->kpis = $payload['kpis'];
        $this->widgets = $payload['widgets'];
        $this->activity = $payload['activity'];
        $this->available = array_keys($payload['widgets']);

        $preference = $this->preference($organization);

        $this->layout = $preference?->layout ?? array_values(array_intersect(
            ExecutiveOverviewService::DEFAULT_ORDER,
            array_keys($payload['widgets'])
        ));
    }

    /**
     * Force a data refresh while keeping the user's layout.
     */
    public function refresh(): void
    {
        $this->loadData($this->organization());
    }

    /**
     * Persist a new widget order (drag and drop).
     *
     * @param  array<int, string>  $order
     */
    public function reorderWidgets(array $order): void
    {
        $allowed = array_flip($this->available);

        $cleaned = array_values(array_filter(
            array_unique($order),
            fn (mixed $key): bool => is_string($key) && isset($allowed[$key])
        ));

        foreach ($this->layout as $key) {
            if (! in_array($key, $cleaned, true) && isset($allowed[$key])) {
                $cleaned[] = $key;
            }
        }

        $this->layout = array_values(array_unique($cleaned));
        $this->savePreference($this->organization());
    }

    /**
     * Hide or reveal a widget.
     */
    public function toggleWidget(string $key): void
    {
        if (! in_array($key, $this->available, true)) {
            return;
        }

        if (($position = array_search($key, $this->layout, true)) !== false) {
            unset($this->layout[$position]);
        } else {
            $this->layout[] = $key;
        }

        $this->layout = array_values(array_unique($this->layout));
        $this->savePreference($this->organization());
    }

    /**
     * The organization this dashboard is scoped to.
     */
    protected function organization(): Organization
    {
        return Organization::query()->findOrFail($this->organizationId);
    }

    /**
     * Resolve the user's layout preference for the organization.
     */
    protected function preference(Organization $organization): ?UserDashboardPreference
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return UserDashboardPreference::query()
            ->where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->first();
    }

    /**
     * Persist the layout, scoped to the user and organization.
     */
    protected function savePreference(Organization $organization): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        UserDashboardPreference::query()->updateOrCreate(
            ['user_id' => $user->id, 'organization_id' => $organization->id],
            ['layout' => $this->layout]
        );

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.dashboard.executive-dashboard');
    }
}
