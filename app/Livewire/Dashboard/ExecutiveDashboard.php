<?php

namespace App\Livewire\Dashboard;

use App\Models\Organization;
use App\Models\UserDashboardPreference;
use App\Services\Dashboard\ExecutiveOverviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
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

    protected ExecutiveOverviewService $service;

    public function mount(Organization $organization, ExecutiveOverviewService $service): void
    {
        $this->service = $service;
        $this->organizationId = $organization->id;
        $this->organizationName = $organization->name;

        // Non-members are never granted the dashboard payload (abort 403).
        $this->organization();

        $this->loadData($organization);
    }

    /**
     * Recompute all aggregates and resolve the personalized layout.
     */
    protected function loadData(Organization $organization): void
    {
        $payload = $this->service->build($organization);

        $this->kpis = $payload['kpis'];
        $this->widgets = $payload['widgets'];
        $this->activity = $payload['activity'];
        $this->available = array_keys($payload['widgets']);
        $this->layout = $this->resolveLayout($organization, $this->available);
        $this->saved = false;
    }

    /**
     * Force a data refresh while keeping the user's layout.
     */
    public function refresh(): void
    {
        $organization = $this->organization();

        if (! $organization) {
            $this->redirect('/setup');

            return;
        }

        ExecutiveOverviewService::forget($organization);
        $this->loadData($organization);
    }

    /**
     * Persist a new widget order (drag and drop).
     *
     * @param  array<int, string>  $order
     */
    public function reorderWidgets(array $order): void
    {
        $this->clearSaved();

        $organization = $this->organization();

        if (! $organization) {
            return;
        }

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
        $this->savePreference($organization);
    }

    /**
     * Hide or reveal a widget.
     */
    public function toggleWidget(string $key): void
    {
        $this->clearSaved();

        if (! in_array($key, $this->available, true)) {
            $this->layout = array_values(array_diff($this->layout, [$key]));

            return;
        }

        if (($position = array_search($key, $this->layout, true)) !== false) {
            unset($this->layout[$position]);
        } else {
            $this->layout[] = $key;
        }

        $this->layout = array_values(array_unique($this->layout));

        $organization = $this->organization();

        if ($organization) {
            $this->savePreference($organization);
        }
    }

    /**
     * The organization this dashboard is scoped to, when the current user is a
     * member of it (or null once the organization no longer exists).
     */
    protected function organization(): ?Organization
    {
        $user = Auth::user();

        if (! $user || ! $this->organizationId) {
            return null;
        }

        $organization = Organization::query()->find($this->organizationId);

        if (! $organization) {
            return null;
        }

        if (! $user->organizations()->whereKey($organization->id)->exists()) {
            abort(403);

            return null;
        }

        return $organization;
    }

    /**
     * Resolve the user's personalized layout, bounded to the currently
     * available widgets so stale keys from removed widgets never surface.
     *
     * @param  array<int, string>  $available
     * @return array<int, string>
     */
    protected function resolveLayout(Organization $organization, array $available): array
    {
        $preference = $this->preference($organization);

        if (! $preference || ! is_array($preference->layout)) {
            return array_values(array_intersect(
                ExecutiveOverviewService::DEFAULT_ORDER,
                $available
            ));
        }

        $this->layout = array_values(array_intersect($preference->layout, $available));

        if (count($this->layout) !== count($preference->layout)) {
            $this->savePreference($organization);
        }

        return $this->layout;
    }

    /**
     * Resolve the user's layout preference for the organization.
     */
    protected function preference(Organization $organization): ?UserDashboardPreference
    {
        $user = Auth::user();

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
        $user = Auth::user();

        if (! $user) {
            return;
        }

        UserDashboardPreference::query()->updateOrCreate(
            ['user_id' => $user->id, 'organization_id' => $organization->id],
            ['layout' => $this->layout]
        );

        $this->saved = true;
    }

    /**
     * Clear the transient "layout saved" indicator before a layout mutation.
     */
    protected function clearSaved(): void
    {
        $this->saved = false;
    }

    public function render(): View
    {
        return view('livewire.dashboard.executive-dashboard');
    }
}
