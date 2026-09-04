(() => {
    'use strict';

    const initialise = (page) => {
        if (page.dataset.sitesEnhanced === 'true') return;

        const workspace = page.querySelector('[data-grid-workspace]');
        const toolbar = workspace?.querySelector('.grid-compact-actions');
        const filterForm = workspace?.querySelector('[data-grid-filter-form]');
        const query = filterForm?.querySelector('[name="q"]');
        const sourceLabel = query?.closest('label');
        const table = page.querySelector('.grid-table');
        const controls = page.querySelector('.grid-pagination-controls');

        if (!workspace || !toolbar || !filterForm || !query || !sourceLabel || !table) return;

        page.dataset.sitesEnhanced = 'true';
        if (!filterForm.id) filterForm.id = 'sites-grid-filter-form';

        const search = document.createElement('label');
        search.className = 'sites-search';
        search.dataset.sitesSearch = '';

        const accessibleLabel = document.createElement('span');
        accessibleLabel.className = 'sr-only';
        accessibleLabel.textContent = 'Search sites';

        query.type = 'search';
        query.placeholder = 'Search sites';
        query.setAttribute('aria-label', 'Search sites');
        query.setAttribute('autocomplete', 'off');
        query.setAttribute('form', filterForm.id);
        search.append(accessibleLabel, query);
        toolbar.prepend(search);
        sourceLabel.hidden = true;

        const summary = Array.from(page.children).find((element) => {
            return element !== workspace && element !== table
                && /^Showing\s/i.test(element.textContent?.trim() ?? '');
        });
        const legacyNavigation = page.querySelector('.grid-workspace__navigation');
        const previous = page.querySelector('[rel="prev"]');
        const next = page.querySelector('[rel="next"]');

        workspace.classList.add('sites-index__workspace');
        table.classList.add('sites-index__table');
        summary?.classList.add('sites-index__summary');

        if (controls) {
            const footer = document.createElement('nav');
            footer.className = 'sites-index__pagination';
            footer.dataset.sitesPagination = '';
            footer.setAttribute('aria-label', 'Sites pagination');

            const previousControl = previous ?? document.createElement('span');
            if (!previous) {
                previousControl.textContent = '« Previous';
                previousControl.setAttribute('aria-disabled', 'true');
                previousControl.classList.add('is-disabled');
            }
            previousControl.classList.add('sites-index__previous');

            const nextControl = next ?? document.createElement('span');
            if (!next) {
                nextControl.textContent = 'Next »';
                nextControl.setAttribute('aria-disabled', 'true');
                nextControl.classList.add('is-disabled');
            }
            nextControl.classList.add('sites-index__next');

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
                    + '[data-sites-pagination]',
                );
                if (paginationOnly && !functional) candidate.remove();
            });
        }

        table.querySelectorAll('tbody tr').forEach((row) => {
            if (row.dataset.siteRowEnhanced === 'true') return;
            row.dataset.siteRowEnhanced = 'true';

            const name = row.querySelector('td[data-grid-column="name"]');
            const code = row.querySelector('td[data-grid-column="code"]');
            const status = row.querySelector('td[data-grid-column="status"]');
            const locale = row.querySelector('td[data-grid-column="locale"]');
            const theme = row.querySelector('td[data-grid-column="theme_code"]');
            const actions = row.querySelector('td[data-grid-column="actions"]');

            name?.classList.add('sites-index__name');
            code?.classList.add('sites-index__code');
            locale?.classList.add('sites-index__locale');
            theme?.classList.add('sites-index__theme');
            actions?.classList.add('sites-index__actions');

            [name, code, locale, theme].forEach((cell) => {
                const value = cell?.textContent?.trim();
                if (cell && value) cell.title = value;
            });

            if (status) {
                const value = status.textContent?.trim() ?? '';
                const badge = document.createElement('span');
                badge.className = `sites-index__status sites-index__status--${value}`;
                badge.textContent = value;
                status.textContent = '';
                status.append(badge);
            }
        });


        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const pageInput = filterForm.querySelector('[name="page"]');
            if (pageInput) pageInput.value = '1';
            filterForm.requestSubmit();
        });
    };

    document.querySelectorAll('.sites-index').forEach(initialise);
})();
