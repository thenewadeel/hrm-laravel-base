import './bootstrap';
import './theme.js';

import { registerDashboard } from './dashboard';

// Livewire 3 bundles Alpine and boots it on DOMContentLoaded, exposing its
// instance as `window.Alpine`. Register the dashboard components on that same
// instance via the `alpine:init` event (fired before Alpine initializes the
// tree), so the component definitions are available when expressions evaluate.
document.addEventListener('alpine:init', () => {
    registerDashboard(window.Alpine);
});
