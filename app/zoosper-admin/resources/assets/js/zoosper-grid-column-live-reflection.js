(() => {
    'use strict';

    const columnKeys = (list) => Array.from(
        list.querySelectorAll(':scope > [data-column-key]')
    ).map((item) => item.dataset.columnKey).filter(Boolean);

    const addMissingHeaderKeys = (table, keys) => {
        const row = table.tHead?.rows?.[0];
        if (!row) return;

        Array.from(row.cells).forEach((cell, index) => {
            if (!cell.dataset.gridColumn && keys[index]) {
                cell.dataset.gridColumn = keys[index];
            }
        });
    };

    const reorderRow = (row, keys) => {
        const keyedCells = new Map(
            Array.from(row.cells)
                .filter((cell) => cell.dataset.gridColumn)
                .map((cell) => [cell.dataset.gridColumn, cell])
        );

        keys.forEach((key) => {
            const cell = keyedCells.get(key);
            if (cell) row.appendChild(cell);
        });
    };

    const reflect = (workspace, keys) => {
        const table = workspace.parentElement?.querySelector('.grid-table')
            ?? document.querySelector('.grid-table');
        if (!table || keys.length === 0) return;

        addMissingHeaderKeys(table, keys);
        Array.from(table.rows).forEach((row) => reorderRow(row, keys));

        const status = workspace.querySelector('.grid-compact-status');
        if (status) {
            status.textContent = 'Unsaved changes';
            status.classList.add('grid-compact-status--dirty');
        }
    };

    const initialise = (workspace) => {
        const list = workspace.querySelector('[data-grid-column-list]');
        if (!list) return;

        let lastOrder = columnKeys(list).join('\u0000');
        let scheduled = false;

        const synchronise = () => {
            if (scheduled) return;
            scheduled = true;

            requestAnimationFrame(() => {
                scheduled = false;
                const keys = columnKeys(list);
                const order = keys.join('\u0000');
                if (order === lastOrder) return;

                lastOrder = order;
                reflect(workspace, keys);
            });
        };

        new MutationObserver(synchronise).observe(list, {childList: true});
        list.addEventListener('drop', synchronise);
        list.addEventListener('dragend', synchronise);
        list.addEventListener('zoosper:grid:columns-reordered', synchronise);
    };

    const boot = () => document
        .querySelectorAll('[data-grid-workspace]')
        .forEach(initialise);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, {once: true});
    } else {
        boot();
    }
})();
