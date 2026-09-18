/**
 * Drag-and-drop reordering for the Executive Dashboard widget grid.
 *
 * Native HTML5 drag events are delegated from the grid root. Widgets are
 * re-arranged live in the DOM while dragging, then the resulting order is
 * pushed to the Livewire component so it persists server-side per user.
 */

export function registerWidgetGrid(Alpine) {
    Alpine.data('widgetGrid', (wire) => ({
        wire,
        dragging: null,

        init() {
            this.rootEl = this.$el;

            this.rootEl.addEventListener('dragstart', (event) => {
                const card = event.target.closest('.dashboard-widget');
                if (!card) {
                    return;
                }

                this.dragging = card;
                card.classList.add('is-dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', card.dataset.widgetKey);
            });

            this.rootEl.addEventListener('dragover', (event) => {
                if (!this.dragging) {
                    return;
                }

                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';

                const card = event.target.closest('.dashboard-widget');
                if (!card || card === this.dragging) {
                    return;
                }

                const rect = card.getBoundingClientRect();
                const after = event.clientY > rect.top + rect.height / 2;

                if (after && card.nextElementSibling !== this.dragging) {
                    this.rootEl.insertBefore(this.dragging, card.nextElementSibling);
                } else if (!after && card.previousElementSibling !== this.dragging) {
                    this.rootEl.insertBefore(this.dragging, card);
                }
            });

            const finish = (event) => {
                if (!this.dragging) {
                    return;
                }

                event.preventDefault();
                this.dragging.classList.remove('is-dragging');
                this.dragging = null;

                const order = Array.from(this.rootEl.querySelectorAll('.dashboard-widget')).map(
                    (node) => node.dataset.widgetKey
                );

                if (typeof this.wire?.reorderWidgets === 'function') {
                    this.wire.reorderWidgets(order);
                }
            };

            this.rootEl.addEventListener('drop', finish);
            this.rootEl.addEventListener('dragend', finish);
        },
    }));
}