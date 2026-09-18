import '../../css/dashboard.css';
import { registerCharts } from './charts';
import { registerDashboardUi } from './ui';
import { registerParticleField } from './particles';
import { registerWidgetGrid } from './drag';

/**
 * Register all Executive Dashboard Alpine components.
 */
export function registerDashboard(Alpine) {
    registerCharts(Alpine);
    registerDashboardUi(Alpine);
    registerParticleField(Alpine);
    registerWidgetGrid(Alpine);
}