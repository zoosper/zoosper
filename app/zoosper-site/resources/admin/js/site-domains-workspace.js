(() => {
    'use strict';

    const initialise = (page) => {
        if (page.dataset.siteDomainsEnhanced === 'true') return;

        const workspace = page.querySelector('[data-grid-workspace]');
        const toolbar = workspace?.querySelector('.grid-compact-actions');
        const filterForm = workspace?.querySelector('[data-grid-filter-form]');
        const query = filterForm?.querySelector('[name="q"]');
        const sourceLabel = query?.closest('label');
        const table = page.querySelector('.grid-table');
        const controls = page.querySelector('.grid-pagination-controls');

        if (!workspace || !toolbar || !filterForm || !query || !sourceLabel || !table) return;

        page.dataset.siteDomainsEnhanced = 'true';
        if (!filterForm.id) filterForm.id = 'site-domains-grid-filter-form';

        const search = document.createElement('label');
        search.className = 'site-domains-search';
        search.dataset.siteDomainsSearch = '';

        const accessibleLabel = document.createElement('span');
        accessibleLabel.className = 'sr-only';
        accessibleLabel.textContent = 'Search domains';

        query.type = 'search';
        query.placeholder = 'Search domains';
        query.setAttribute('aria-label', 'Search domains');
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

        workspace.classList.add('site-domains-index__workspace');
        table.classList.add('site-domains-index__table');
        summary?.classList.add('site-domains-index__summary');

        if (controls) {
            const footer = document.createElement('nav');
            footer.className = 'site-domains-index__pagination';
            footer.dataset.siteDomainsPagination = '';
            footer.setAttribute('aria-label', 'Site Domains pagination');

            const previousControl = previous ?? document.createElement('span');
            if (!previous) {
                previousControl.textContent = '« Previous';
                previousControl.setAttribute('aria-disabled', 'true');
                previousControl.classList.add('is-disabled');
            }
            previousControl.classList.add('site-domains-index__previous');

            const nextControl = next ?? document.createElement('span');
            if (!next) {
                nextControl.textContent = 'Next »';
                nextControl.setAttribute('aria-disabled', 'true');
                nextControl.classList.add('is-disabled');
            }
            nextControl.classList.add('site-domains-index__next');

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
                    + '[data-site-domains-pagination]',
                );
                if (paginationOnly && !functional) candidate.remove();
            });
        }

        table.querySelectorAll('tbody tr').forEach((row) => {
            if (row.dataset.siteDomainRowEnhanced === 'true') return;
            row.dataset.siteDomainRowEnhanced = 'true';

            const host = row.querySelector('td[data-grid-column="host"]');
            const site = row.querySelector('td[data-grid-column="site_name"]');
            const primary = row.querySelector('td[data-grid-column="is_primary"]');
            const actions = row.querySelector('td[data-grid-column="actions"]');

            host?.classList.add('site-domains-index__host');
            site?.classList.add('site-domains-index__site');
            actions?.classList.add('site-domains-index__actions');

            [host, site].forEach((cell) => {
                const value = cell?.textContent?.trim();
                if (cell && value) cell.title = value;
            });

            if (primary) {
                const value = primary.textContent?.trim() ?? '';
                const badge = document.createElement('span');
                badge.className = value === 'Yes'
                    ? 'site-domains-index__primary site-domains-index__primary--yes'
                    : 'site-domains-index__primary site-domains-index__primary--no';
                badge.textContent = value;
                primary.textContent = '';
                primary.append(badge);
            }
        });

        const shellTitle = document.querySelector('.admin-topbar__title');
        if (shellTitle?.textContent?.trim() === 'Site Domains') shellTitle.hidden = true;

        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const pageInput = filterForm.querySelector('[name="page"]');
            if (pageInput) pageInput.value = '1';
            filterForm.requestSubmit();
        });
    };

    document.querySelectorAll('.site-domains-index').forEach(initialise);
})();
