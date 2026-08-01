(() => {
    'use strict';

    const initialise = (workspace) => {
        const content = workspace.closest('main') ?? document;
        const table = content.querySelector('.grid-table');
        const controls = Array.from(workspace.querySelectorAll('[data-column-key]'));

        const sync = () => {
            const visible = new Set(
                Array.from(workspace.querySelectorAll('[name="visible_columns[]"]:checked'))
                    .map((input) => input.value)
            );

            if (table) {
                controls.forEach((control, index) => {
                    const key = control.dataset.columnKey;
                    table.querySelectorAll(`[data-grid-column="${CSS.escape(key)}"]`)
                        .forEach((cell) => cell.hidden = !visible.has(key));
                    table.querySelectorAll(`tr > :nth-child(${index + 1})`)
                        .forEach((cell) => cell.hidden = !visible.has(key));
                });
            }

            workspace.querySelectorAll('[data-grid-filter-columns]').forEach((field) => {
                const dependencies = field.dataset.gridFilterColumns.split(/\s+/).filter(Boolean);
                field.hidden = dependencies.length > 0
                    && !dependencies.some((key) => visible.has(key));
            });
        };

        workspace.querySelectorAll('[name="visible_columns[]"]').forEach((input) => {
            input.addEventListener('change', sync);
        });
        sync();
    };

    document.querySelectorAll('[data-grid-workspace]').forEach(initialise);
})();

/* BEGIN GRID MISSING COLUMN RESTORE */
/*
 * A server-hidden column has no header or cells in the current DOM. Unchecking
 * can therefore preview immediately, but re-checking requires one canonical GET
 * request so the server can render the missing values safely.
 */
document.querySelectorAll('[data-grid-workspace]').forEach((workspace) => {
    const form = workspace.querySelector('[data-grid-filter-form]');
    const content = workspace.closest('main') ?? document;
    const table = content.querySelector('.grid-table');

    workspace.querySelectorAll('[name="visible_columns[]"]').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            if (!checkbox.checked || !form || !table) {
                return;
            }

            const key = checkbox.value;
            const selector = `[data-grid-column="${CSS.escape(key)}"]`;
            if (table.querySelector(selector) === null) {
                const page = form.querySelector('[name="page"]');
                if (page) {
                    page.value = '1';
                }
                form.requestSubmit();
            }
        });
    });
});
/* END GRID MISSING COLUMN RESTORE */
