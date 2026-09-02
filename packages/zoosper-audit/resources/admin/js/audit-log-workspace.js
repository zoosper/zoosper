(() => {
    'use strict';

    const initialise = (page) => {
        const workspace = page.querySelector('[data-grid-workspace]');
        const toolbar = workspace?.querySelector('.grid-compact-actions');
        const filterForm = workspace?.querySelector('[data-grid-filter-form]');
        const query = filterForm?.querySelector('[name="q"]');
        const sourceLabel = query?.closest('label');

        if (!workspace || !toolbar || !filterForm || !query || !sourceLabel) {
            return;
        }

        if (workspace.querySelector('[data-audit-log-search]')) {
            return;
        }

        if (!filterForm.id) {
            filterForm.id = 'audit-log-grid-filter-form';
        }

        const search = document.createElement('label');
        search.className = 'audit-log-search';
        search.dataset.auditLogSearch = '';

        const accessibleLabel = document.createElement('span');
        accessibleLabel.className = 'sr-only';
        accessibleLabel.textContent = 'Search action, actor or summary';

        query.type = 'search';
        query.placeholder = 'Search action, actor or summary';
        query.setAttribute('aria-label', 'Search action, actor or summary');
        query.setAttribute('autocomplete', 'off');
        query.setAttribute('form', filterForm.id);

        search.append(accessibleLabel, query);
        toolbar.prepend(search);
        sourceLabel.hidden = true;

        const table = page.querySelector('.grid-table');
        const summary = Array.from(page.children).find((element) => {
            return element !== workspace
                && element !== table
                && /^Showing\s/i.test(element.textContent?.trim() ?? '');
        });
        const controls = page.querySelector('.grid-pagination-controls');
        const legacyNavigation = page.querySelector('.grid-workspace__navigation');
        const previous = page.querySelector('[rel="prev"]');
        const next = page.querySelector('[rel="next"]');

        workspace.classList.add('audit-log-index__workspace');
        table?.classList.add('audit-log-index__table');
        summary?.classList.add('audit-log-index__summary');

        if (table && controls) {
            const footer = document.createElement('nav');
            footer.className = 'audit-log-index__pagination';
            footer.dataset.auditLogPagination = '';
            footer.setAttribute('aria-label', 'Audit Log pagination');

            const previousControl = previous ?? document.createElement('span');
            if (!previous) {
                previousControl.textContent = '« Previous';
                previousControl.setAttribute('aria-disabled', 'true');
                previousControl.classList.add('is-disabled');
            }
            previousControl.classList.add('audit-log-index__previous');

            const nextControl = next ?? document.createElement('span');
            if (!next) {
                nextControl.textContent = 'Next »';
                nextControl.setAttribute('aria-disabled', 'true');
                nextControl.classList.add('is-disabled');
            }
            nextControl.classList.add('audit-log-index__next');

            footer.append(previousControl, controls, nextControl);
            table.insertAdjacentElement('afterend', footer);
            legacyNavigation?.remove();

            Array.from(page.children).forEach((candidate) => {
                if (candidate === footer) return;
                const relation = footer.compareDocumentPosition(candidate);
                if (!(relation & Node.DOCUMENT_POSITION_FOLLOWING)) return;
                const text = (candidate.textContent ?? '')
                    .replace(/\s+/g, ' ')
                    .replace(/[«»]/g, '')
                    .trim();
                const paginationOnly = /^(Previous\s*)?(Next)?$/i.test(text)
                    && /Previous|Next/i.test(text);
                const functional = candidate.querySelector(
                    'form, input, select, button, table, [data-grid-workspace], '
                    + '[data-audit-log-pagination]',
                );
                if (paginationOnly && !functional) candidate.remove();
            });
        }

        table?.querySelectorAll('tbody tr').forEach((row) => {
            if (row.dataset.auditLogRowEnhanced === 'true') return;
            row.dataset.auditLogRowEnhanced = 'true';

            const actor = row.querySelector('td[data-grid-column="actor_email"]');
            const action = row.querySelector('td[data-grid-column="action"]');
            const entity = row.querySelector('td[data-grid-column="entity_type"]');
            const summaryCell = row.querySelector('td[data-grid-column="summary"]');

            actor?.classList.add('audit-log-index__actor');
            action?.classList.add('audit-log-index__action');
            entity?.classList.add('audit-log-index__entity');
            summaryCell?.classList.add('audit-log-index__row-summary');

            [actor, action, entity, summaryCell].forEach((cell) => {
                const value = cell?.textContent?.trim();
                if (cell && value) cell.title = value;
            });
        });

        const shellTitle = document.querySelector('.admin-topbar__title');
        if (shellTitle?.textContent?.trim() === 'Audit Log') {
            shellTitle.hidden = true;
            shellTitle.dataset.auditLogDuplicateShellTitle = '';
        }

        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();

            const pageInput = filterForm.querySelector('[name="page"]');

            if (pageInput) {
                pageInput.value = '1';
            }

            filterForm.requestSubmit();
        });
    };

    document.querySelectorAll('.audit-log-index').forEach(initialise);
})();
