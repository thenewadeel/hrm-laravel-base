/**
 * Small Alpine helpers for the Executive Dashboard (live clock etc).
 */
export function registerDashboardUi(Alpine) {
    Alpine.data('liveClock', () => ({
        liveDate: '',
        timer: null,
        init() {
            this.tick();
            this.timer = setInterval(() => this.tick(), 1000);
        },
        tick() {
            const now = new Date();
            const date = now.toLocaleDateString(undefined, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
            const time = now.toLocaleTimeString(undefined, {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
            this.liveDate = `${date} · ${time}`;
        },
        destroy() {
            if (this.timer) {
                clearInterval(this.timer);
            }
        },
    }));
}