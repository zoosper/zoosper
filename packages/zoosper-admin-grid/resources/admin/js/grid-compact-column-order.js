(() => {
    'use strict';

    const LIST = '.grid-compact-columns';
    const ITEM = '.grid-compact-column[data-column-key]';
    const LOCKED_KEYS = new Set(['id', 'actions']);

    const keyOf = (item) => (item.getAttribute('data-column-key') ?? '').trim();
    const isMovable = (item) => item.matches(ITEM) && !LOCKED_KEYS.has(keyOf(item));

    const syncOrder = (list) => {
        const keys = Array.from(list.querySelectorAll(ITEM), keyOf).filter(Boolean);
        const form = list.closest('form') ?? list.closest('[data-grid-workspace]')?.querySelector('form');
        if (!(form instanceof HTMLFormElement)) return;

        const inputs = Array.from(form.querySelectorAll('input[name="column_order[]"]'));
        while (inputs.length < keys.length) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'column_order[]';
            form.appendChild(input);
            inputs.push(input);
        }
        inputs.forEach((input, index) => {
            if (index < keys.length) input.value = keys[index];
            else input.remove();
        });
        list.dispatchEvent(new CustomEvent('grid:column-order-changed', {
            bubbles: true,
            detail: { order: keys },
        }));
    };

    document.querySelectorAll(LIST).forEach((list) => {
        let dragging = null;

        list.querySelectorAll(ITEM).forEach((item) => {
            const movable = isMovable(item);
            item.draggable = movable;
            item.setAttribute('aria-grabbed', 'false');
            item.classList.toggle('is-column-locked', !movable);

            if (!movable) return;

            item.addEventListener('dragstart', (event) => {
                dragging = item;
                item.classList.add('is-dragging');
                item.setAttribute('aria-grabbed', 'true');
                if (event.dataTransfer !== null) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', keyOf(item));
                }
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('is-dragging');
                item.setAttribute('aria-grabbed', 'false');
                dragging = null;
                syncOrder(list);
            });

            item.addEventListener('dragenter', (event) => {
                if (dragging !== null && dragging !== item) event.preventDefault();
            });

            item.addEventListener('dragover', (event) => {
                if (dragging === null || dragging === item || !isMovable(item)) return;
                event.preventDefault();
                if (event.dataTransfer !== null) event.dataTransfer.dropEffect = 'move';
            });

            item.addEventListener('drop', (event) => {
                if (dragging === null || dragging === item || !isMovable(item)) return;
                event.preventDefault();
                const box = item.getBoundingClientRect();
                const horizontal = list.scrollWidth > list.clientWidth || box.width < list.clientWidth * 0.8;
                const before = horizontal
                    ? event.clientX < box.left + box.width / 2
                    : event.clientY < box.top + box.height / 2;
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
            if (!(item instanceof HTMLElement) || !isMovable(item)) return;

            const movable = Array.from(list.querySelectorAll(ITEM)).filter(isMovable);
            const index = movable.indexOf(item);
            const direction = button.getAttribute('data-grid-column-move');
            if (direction === 'up' && index > 0) {
                list.insertBefore(item, movable[index - 1]);
            } else if (direction === 'down' && index >= 0 && index < movable.length - 1) {
                list.insertBefore(movable[index + 1], item);
            } else {
                return;
            }
            syncOrder(list);
            button.focus({ preventScroll: true });
        });

        syncOrder(list);
    });
})();
