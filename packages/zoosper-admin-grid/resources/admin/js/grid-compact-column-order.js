(() => {
    'use strict';

    const LIST_SELECTOR = '[data-grid-column-list]';
    const ITEM_SELECTOR = '.grid-compact-column[data-column-key]';
    const LOCKED_KEYS = new Set(['id', 'actions']);
    const BOUND_ATTRIBUTE = 'data-zoosper-column-order-bound';

    const keyOf = (item) => (item.dataset.columnKey ?? '').trim();
    const isMovable = (item) => item.matches(ITEM_SELECTOR) && !LOCKED_KEYS.has(keyOf(item));
    const keysFrom = (list) => Array.from(list.querySelectorAll(ITEM_SELECTOR), keyOf).filter(Boolean);

    const formFor = (list) => list.closest('form')
        ?? list.closest('[data-grid-workspace]')?.querySelector('form')
        ?? null;

    const replaceVisibleInputs = (form, values) => {
        form.querySelectorAll('input[name="visible_columns[]"]').forEach((input) => input.remove());
        values.forEach((value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'visible_columns[]';
            input.value = value;
            form.appendChild(input);
        });
    };

    const replaceColumnOrderInputs = (form, values) => {
        form.querySelectorAll('input[name="column_order[]"]').forEach((input) => input.remove());
        values.forEach((value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'column_order[]';
            input.value = value;
            form.appendChild(input);
        });
    };

    const syncPersistedColumnState = (list) => {
        const workspace = list.closest('[data-grid-workspace]');
        const settings = workspace?.nextElementSibling?.matches('[data-grid-settings]')
            ? workspace.nextElementSibling
            : null;
        if (!settings) return;

        const order = keysFrom(list);
        const visible = Array.from(list.querySelectorAll(`${ITEM_SELECTOR} input[name="visible_columns[]"]:checked`))
            .map((input) => input.value.trim())
            .filter(Boolean);
        settings.querySelectorAll('[data-grid-column-state-form]').forEach((form) => {
            if (!(form instanceof HTMLFormElement)) return;
            replaceVisibleInputs(form, visible);
            replaceColumnOrderInputs(form, order);
        });
    };

    const syncOrderInputs = (list) => {
        const form = formFor(list);
        if (!(form instanceof HTMLFormElement)) return;

        const keys = keysFrom(list);
        replaceColumnOrderInputs(form, keys);
        syncPersistedColumnState(list);
    };

    const tableFor = (list) => {
        const workspace = list.closest('[data-grid-workspace]');
        const content = workspace?.closest('.admin-content') ?? document;
        return content.querySelector('.grid-table');
    };


    const reflectTableOrder = (list) => {
        const keys = keysFrom(list);
        const table = tableFor(list);
        if (!table || keys.length === 0) return;


        Array.from(table.rows).forEach((row) => {
            const cells = new Map(Array.from(row.cells)
                .filter((cell) => cell.dataset.gridColumn)
                .map((cell) => [cell.dataset.gridColumn, cell]));

            keys.forEach((key) => {
                const cell = cells.get(key);
                if (cell) row.appendChild(cell);
            });
        });
    };

    const markDirty = (list) => {
        const workspace = list.closest('[data-grid-workspace]');
        const status = workspace?.querySelector('.grid-compact-status');
        if (!status) return;

        status.textContent = 'Unsaved changes';
        status.classList.add('grid-compact-status--dirty');
    };

    const publishOrder = (list) => {
        const order = keysFrom(list);
        syncOrderInputs(list);
        reflectTableOrder(list);
        markDirty(list);
        list.dispatchEvent(new CustomEvent('zoosper:grid:columns-reordered', {
            bubbles: true,
            detail: {order},
        }));
    };

    const bind = (list) => {
        if (list.hasAttribute(BOUND_ATTRIBUTE)) return;
        list.setAttribute(BOUND_ATTRIBUTE, 'true');

        let dragging = null;

        list.querySelectorAll(ITEM_SELECTOR).forEach((item) => {
            const movable = isMovable(item);
            item.draggable = movable;
            item.classList.toggle('is-grid-column-locked', !movable);
            item.setAttribute('aria-grabbed', 'false');

            if (!movable) return;

            item.addEventListener('dragstart', (event) => {
                dragging = item;
                item.classList.add('is-grid-column-dragging');
                item.setAttribute('aria-grabbed', 'true');
                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', keyOf(item));
                }
            });

            item.addEventListener('dragover', (event) => {
                if (dragging === null || dragging === item || !isMovable(item)) return;
                event.preventDefault();
                if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
            });

            item.addEventListener('drop', (event) => {
                if (dragging === null || dragging === item || !isMovable(item)) return;
                event.preventDefault();
                const box = item.getBoundingClientRect();
                const horizontal = list.scrollWidth > list.clientWidth || box.width < list.clientWidth * 0.8;
                const before = horizontal
                    ? event.clientX < box.left + box.width / 2
                    : event.clientY < box.top + box.height / 2;
                list.insertBefore(dragging, before ? item : item.nextElementSibling);
                publishOrder(list);
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('is-grid-column-dragging');
                item.setAttribute('aria-grabbed', 'false');
                dragging = null;
            });
        });

        list.addEventListener('change', (event) => {
            const input = event.target;
            if (!(input instanceof HTMLInputElement) || input.name !== 'visible_columns[]') return;
            syncPersistedColumnState(list);
            markDirty(list);
        });

        list.addEventListener('click', (event) => {
            const button = event.target instanceof Element
                ? event.target.closest('[data-grid-column-move]')
                : null;
            if (!(button instanceof HTMLButtonElement)) return;

            const item = button.closest(ITEM_SELECTOR);
            if (!(item instanceof HTMLElement) || !isMovable(item)) return;

            const movableItems = Array.from(list.querySelectorAll(ITEM_SELECTOR)).filter(isMovable);
            const index = movableItems.indexOf(item);
            const direction = button.dataset.gridColumnMove;

            if (direction === 'up' && index > 0) {
                list.insertBefore(item, movableItems[index - 1]);
            } else if (direction === 'down' && index >= 0 && index < movableItems.length - 1) {
                list.insertBefore(movableItems[index + 1], item);
            } else {
                return;
            }

            publishOrder(list);
            button.focus({preventScroll: true});
        });

        syncOrderInputs(list);
    };

    const boot = () => document.querySelectorAll(LIST_SELECTOR).forEach(bind);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, {once: true});
    } else {
        boot();
    }
})();
