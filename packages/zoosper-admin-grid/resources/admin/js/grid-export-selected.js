(() => {
    'use strict';

    const quoteCsv = (value) => `"${String(value).replaceAll('"', '""')}"`;
    const safeFilePart = (value) => value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 80) || 'grid';

    const bind = () => {
        const table = document.querySelector('table.grid-has-selection');
        const bar = document.querySelector('[data-grid-selection-bar]');
        const action = bar?.querySelector('select[aria-label="Bulk actions"]');
        if (!table || !bar || !action || action.dataset.gridExportSelectedBound === 'true') return;

        action.dataset.gridExportSelectedBound = 'true';
        action.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Bulk actions';
        const exportOption = document.createElement('option');
        exportOption.value = 'export-selected';
        exportOption.textContent = 'Export selected';
        action.append(placeholder, exportOption);

        const exportSelected = () => {
            const rows = Array.from(table.querySelectorAll('tbody > tr.is-selected'));
            if (rows.length === 0) return;

            const headers = Array.from(table.querySelectorAll('thead th'))
                .slice(1)
                .map((cell) => cell.textContent.trim() || 'Column');
            const records = rows.map((row) => Array.from(row.querySelectorAll(':scope > td'))
                .slice(1)
                .map((cell) => cell.textContent.replace(/\s+/g, ' ').trim()));

            const csv = [headers, ...records]
                .map((record) => record.map(quoteCsv).join(','))
                .join('\r\n');
            const blob = new Blob([`\uFEFF${csv}`], {type: 'text/csv;charset=utf-8'});
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            const heading = document.querySelector('h1, main h2, header h1')?.textContent.trim() || 'grid';
            link.href = url;
            link.download = `${safeFilePart(heading)}-selected.csv`;
            link.hidden = true;
            document.body.append(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
            action.value = '';
        };

        action.addEventListener('change', () => {
            if (action.value === 'export-selected') exportSelected();
        });
    };

    bind();
    new MutationObserver(bind).observe(document.body, {childList: true, subtree: true});
})();
