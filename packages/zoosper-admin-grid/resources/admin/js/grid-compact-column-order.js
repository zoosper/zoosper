(() => {
    'use strict';

    const ITEM = '.grid-compact-column[data-column-key]';
    const MOVABLE = `${ITEM}[draggable="true"]`;

    const nearestList = (item) => item.closest('.grid-compact-columns');

    const syncOrder = (list) => {
        list.querySelectorAll(ITEM).forEach((item) => {
            const key = item.getAttribute('data-column-key');
            const input = item.querySelector('input[name="column_order[]"]');
            if (key !== null && input instanceof HTMLInputElement) {
                input.value = key;
            }
        });
        list.dispatchEvent(new CustomEvent('grid:column-order-changed', { bubbles: true }));
    };

    const previousMovable = (item) => {
        let candidate = item.previousElementSibling;
        while (candidate !== null) {
            if (candidate.matches(MOVABLE)) return candidate;
            candidate = candidate.previousElementSibling;
        }
        return null;
    };

    const nextMovable = (item) => {
        let candidate = item.nextElementSibling;
        while (candidate !== null) {
            if (candidate.matches(MOVABLE)) return candidate;
            candidate = candidate.nextElementSibling;
        }
        return null;
    };

    document.querySelectorAll('.grid-compact-columns').forEach((list) => {
        let dragging = null;

        list.querySelectorAll(MOVABLE).forEach((item) => {
            item.addEventListener('dragstart', (event) => {
                dragging = item;
                item.classList.add('is-dragging');
                if (event.dataTransfer !== null) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', item.getAttribute('data-column-key') ?? '');
                }
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('is-dragging');
                dragging = null;
                syncOrder(list);
            });

            item.addEventListener('dragover', (event) => {
                if (dragging === null || dragging === item) return;
                event.preventDefault();
                if (event.dataTransfer !== null) event.dataTransfer.dropEffect = 'move';
            });

            item.addEventListener('drop', (event) => {
                if (dragging === null || dragging === item) return;
                event.preventDefault();
                const box = item.getBoundingClientRect();
                const before = event.clientX < box.left + (box.width / 2);
                list.insertBefore(dragging, before ? item : item.nextSibling);
                syncOrder(list);
            });
        });

        list.addEventListener('click', (event) => {
            const button = event.target instanceof Element
                ? event.target.closest('[data-grid-column-move]')
                : null;
            if (!(button instanceof HTMLButtonElement)) return;

            const item = button.closest(ITEM);
            if (!(item instanceof HTMLElement) || item.getAttribute('draggable') !== 'true') return;

            const direction = button.getAttribute('data-grid-column-move');
            if (direction === 'up') {
                const previous = previousMovable(item);
                if (previous !== null) list.insertBefore(item, previous);
            } else if (direction === 'down') {
                const next = nextMovable(item);
                if (next !== null) list.insertBefore(next, item);
            } else {
                return;
            }

            syncOrder(list);
            item.focus({ preventScroll: true });
        });
    });
})();
