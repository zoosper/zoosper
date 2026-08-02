(() => {
    'use strict';

    const listSelector = '[data-grid-column-list]';
    const itemSelector = '.grid-compact-column[data-column-key]';
    const lockedKeys = new Set(['id', 'actions']);
    const keyOf = (item) => (item.dataset.columnKey ?? '').trim();
    const movable = (item) => item.matches(itemSelector) && !lockedKeys.has(keyOf(item));

    const sync = (list) => {
        const form = list.closest('form');
        if (!(form instanceof HTMLFormElement)) return;
        const keys = Array.from(list.querySelectorAll(itemSelector), keyOf).filter(Boolean);
        const inputs = Array.from(form.querySelectorAll('input[name="column_order[]"]'));
        inputs.forEach((input, index) => {
            if (index < keys.length) input.value = keys[index];
        });
    };

    document.querySelectorAll(listSelector).forEach((list) => {
        let active = null;

        list.querySelectorAll(itemSelector).forEach((item) => {
            const enabled = movable(item);
            item.draggable = enabled;
            item.classList.toggle('is-grid-column-locked', !enabled);
            item.setAttribute('aria-grabbed', 'false');
            if (!enabled) return;

            item.addEventListener('dragstart', (event) => {
                active = item;
                item.classList.add('is-grid-column-dragging');
                item.setAttribute('aria-grabbed', 'true');
                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', keyOf(item));
                }
            });

            item.addEventListener('dragover', (event) => {
                if (active === null || active === item || !movable(item)) return;
                event.preventDefault();
                if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
            });

            item.addEventListener('drop', (event) => {
                if (active === null || active === item || !movable(item)) return;
                event.preventDefault();
                const box = item.getBoundingClientRect();
                const before = event.clientX < box.left + box.width / 2;
                list.insertBefore(active, before ? item : item.nextElementSibling);
                sync(list);
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('is-grid-column-dragging');
                item.setAttribute('aria-grabbed', 'false');
                active = null;
                sync(list);
            });
        });

        sync(list);
    });
})();


/* Zoosper Phase 4ZE: live table reflection (bundled into loaded drag bridge). */
(() => {
    'use strict';

    const marker = 'data-zoosper-live-reflection';

    const keysFrom = (list) => Array.from(list.querySelectorAll('[data-column-key]'))
        .map((item) => item.dataset.columnKey)
        .filter(Boolean);

    const tableFor = (workspace) => {
        const content = workspace.closest('.admin-content') ?? document;
        return content.querySelector('.grid-table');
    };

    const keyMissingHeaders = (table, keys) => {
        const header = table.tHead?.rows?.[0];
        if (!header) return;

        Array.from(header.cells).forEach((cell, index) => {
            if (!cell.dataset.gridColumn && keys[index]) {
                cell.dataset.gridColumn = keys[index];
            }
        });
    };

    const reflect = (workspace, list) => {
        const keys = keysFrom(list);
        const table = tableFor(workspace);
        if (!table || keys.length === 0) return;

        keyMissingHeaders(table, keys);

        Array.from(table.rows).forEach((row) => {
            const cells = new Map(Array.from(row.cells)
                .filter((cell) => cell.dataset.gridColumn)
                .map((cell) => [cell.dataset.gridColumn, cell]));

            keys.forEach((key) => {
                const cell = cells.get(key);
                if (cell) row.appendChild(cell);
            });
        });

        const status = workspace.querySelector('.grid-compact-status');
        if (status) {
            status.textContent = 'Unsaved changes';
            status.classList.add('grid-compact-status--dirty');
        }
    };

    const bind = (workspace) => {
        const list = workspace.querySelector('[data-grid-column-list]');
        if (!list || list.hasAttribute(marker)) return;
        list.setAttribute(marker, 'true');

        let previous = keysFrom(list).join('|');
        let pending = false;
        const sync = () => {
            if (pending) return;
            pending = true;
            requestAnimationFrame(() => {
                pending = false;
                const current = keysFrom(list).join('|');
                if (current === previous) return;
                previous = current;
                reflect(workspace, list);
            });
        };

        new MutationObserver(sync).observe(list, {childList: true});
        list.addEventListener('drop', sync);
        list.addEventListener('dragend', sync);
        list.addEventListener('zoosper:grid:columns-reordered', sync);
    };

    const boot = () => document.querySelectorAll('[data-grid-workspace]').forEach(bind);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, {once: true});
    } else {
        boot();
    }
})();
