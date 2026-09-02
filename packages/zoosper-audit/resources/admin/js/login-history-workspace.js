(() => {
    'use strict';

    const initialise = (page) => {
        if (page.dataset.loginHistoryEnhanced === 'true') return;

        const workspace = page.querySelector('[data-grid-workspace]');
        const toolbar = workspace?.querySelector('.grid-compact-actions');
        const filterForm = workspace?.querySelector('[data-grid-filter-form]');
        const query = filterForm?.querySelector('[name="q"]');
        const sourceLabel = query?.closest('label');
        const table = page.querySelector('.grid-table');
        const controls = page.querySelector('.grid-pagination-controls');

        if (!workspace || !toolbar || !filterForm || !query || !sourceLabel || !table) {
            return;
        }

        page.dataset.loginHistoryEnhanced = 'true';

        if (!filterForm.id) filterForm.id = 'login-history-grid-filter-form';

        const search = document.createElement('label');
        search.className = 'login-history-search';
        search.dataset.loginHistorySearch = '';

        const accessibleLabel = document.createElement('span');
        accessibleLabel.className = 'sr-only';
        accessibleLabel.textContent = 'Search login email';

        query.type = 'search';
        query.placeholder = 'Search email';
        query.setAttribute('aria-label', 'Search login email');
        query.setAttribute('autocomplete', 'off');
        query.setAttribute('form', filterForm.id);

        search.append(accessibleLabel, query);
        toolbar.prepend(search);
        sourceLabel.hidden = true;

        const summary = Array.from(page.children).find((element) => {
            return element !== workspace
                && element !== table
                && /^Showing\s/i.test(element.textContent?.trim() ?? '');
        });
        const legacyNavigation = page.querySelector('.grid-workspace__navigation');
        const previous = page.querySelector('[rel="prev"]');
        const next = page.querySelector('[rel="next"]');

        workspace.classList.add('login-history-index__workspace');
        table.classList.add('login-history-index__table');
        summary?.classList.add('login-history-index__summary');

        if (controls) {
            const footer = document.createElement('nav');
            footer.className = 'login-history-index__pagination';
            footer.dataset.loginHistoryPagination = '';
            footer.setAttribute('aria-label', 'Login History pagination');

            const previousControl = previous ?? document.createElement('span');
            if (!previous) {
                previousControl.textContent = '« Previous';
                previousControl.setAttribute('aria-disabled', 'true');
                previousControl.classList.add('is-disabled');
            }
            previousControl.classList.add('login-history-index__previous');

            const nextControl = next ?? document.createElement('span');
            if (!next) {
                nextControl.textContent = 'Next »';
                nextControl.setAttribute('aria-disabled', 'true');
                nextControl.classList.add('is-disabled');
            }
            nextControl.classList.add('login-history-index__next');

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
                    + '[data-login-history-pagination]',
                );
                if (paginationOnly && !functional) candidate.remove();
            });
        }

        table.querySelectorAll('tbody tr').forEach((row) => {
            if (row.dataset.loginHistoryRowEnhanced === 'true') return;
            row.dataset.loginHistoryRowEnhanced = 'true';

            const email = row.querySelector('td[data-grid-column="email"]');
            const statusCell = row.querySelector('td[data-grid-column="status"]');
            const ip = row.querySelector('td[data-grid-column="ip_address"]');

            email?.classList.add('login-history-index__email');
            ip?.classList.add('login-history-index__ip');

            [email, ip].forEach((cell) => {
                const value = cell?.textContent?.trim();
                if (cell && value) cell.title = value;
            });

            if (statusCell) {
                const value = statusCell.textContent?.trim() ?? '';
                const code = statusCell.querySelector('code');
                const pill = code ?? document.createElement('span');
                if (!code) {
                    statusCell.textContent = '';
                    pill.textContent = value;
                    statusCell.append(pill);
                }
                pill.className = `login-history-index__status login-history-index__status--${value}`;
                pill.title = value;
            }
        });

        const shellTitle = document.querySelector('.admin-topbar__title');
        if (shellTitle?.textContent?.trim() === 'Login History') {
            shellTitle.hidden = true;
            shellTitle.dataset.loginHistoryDuplicateShellTitle = '';
        }

        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const pageInput = filterForm.querySelector('[name="page"]');
            if (pageInput) pageInput.value = '1';
            filterForm.requestSubmit();
        });
    };

    document.querySelectorAll('.login-history-index').forEach(initialise);
})();
