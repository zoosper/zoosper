(() => {
    'use strict';

    const initialise = (page) => {
        const workspace = page.querySelector('[data-grid-workspace]');
        const toolbar = workspace?.querySelector('.grid-compact-actions');
        const filterForm = workspace?.querySelector('[data-grid-filter-form]');
        const query = filterForm?.querySelector('[name="q"]');
        const sourceLabel = query?.closest('label');

        if (!workspace || !toolbar || !filterForm || !query || !sourceLabel) return;
        if (workspace.querySelector('[data-page-grid-search]')) return;

        const search = document.createElement('label');
        search.className = 'page-grid-search';
        search.dataset.pageGridSearch = '';

        const accessibleLabel = document.createElement('span');
        accessibleLabel.className = 'sr-only';
        accessibleLabel.textContent = 'Search pages by title or slug';

        query.type = 'search';
        query.placeholder = 'Search pages by title or slug';
        query.setAttribute('aria-label', 'Search pages by title or slug');
        query.setAttribute('autocomplete', 'off');

        if (!filterForm.id) {
            filterForm.id = 'page-grid-filter-form';
        }
        query.setAttribute('form', filterForm.id);

        search.append(accessibleLabel, query);
        toolbar.prepend(search);
        sourceLabel.hidden = true;

        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const pageInput = filterForm.querySelector('[name="page"]');
            if (pageInput) pageInput.value = '1';
            filterForm.requestSubmit();
        });

        const table = page.querySelector('.grid-table');
        const navigation = page.querySelector(
            '.grid-workspace__navigation, nav[aria-label*="Pagination"]',
        );
        const paginationControls = page.querySelector('.grid-pagination-controls');
        const previous = page.querySelector('[rel="prev"]');
        const next = page.querySelector('[rel="next"]');
        const summary = Array.from(page.children).find((element) => {
            return element !== workspace
                && element !== table
                && /^Showing\s/i.test(element.textContent?.trim() ?? '');
        });

        page.classList.add('page-grid-index--enhanced');
        workspace.classList.add('page-grid-index__workspace');

        table?.classList.add('page-grid-index__table');
        summary?.classList.add('page-grid-index__summary');

        const titleHeader = table?.querySelector('th[data-grid-column="title"]');
        const slugHeader = table?.querySelector('th[data-grid-column="slug"]');

        if (titleHeader && slugHeader) {
            const titleSort = titleHeader.querySelector('a');
            if (titleSort) {
                const sortMarker = titleSort.textContent?.includes('▼') ? ' ▼'
                    : titleSort.textContent?.includes('▲') ? ' ▲' : '';
                titleSort.textContent = `Title & slug${sortMarker}`;
            } else {
                titleHeader.textContent = 'Title & slug';
            }
            slugHeader.classList.add('page-grid-index__slug-column');
            slugHeader.setAttribute('aria-hidden', 'true');
        }

        table?.querySelectorAll('tbody tr').forEach((row) => {
            if (row.dataset.pageGridRowEnhanced === 'true') return;
            row.dataset.pageGridRowEnhanced = 'true';

            const titleCell = row.querySelector('td[data-grid-column="title"]');
            const slugCell = row.querySelector('td[data-grid-column="slug"]');
            const statusCell = row.querySelector('td[data-grid-column="status"]');
            const siteCell = row.querySelector('td[data-grid-column="site_name"]');
            const actionsCell = row.querySelector('td[data-grid-column="actions"]');

            if (titleCell && slugCell) {
                titleCell.classList.add('page-grid-index__identity');
                const slug = document.createElement('span');
                slug.className = 'page-grid-index__slug';
                slug.textContent = `/${slugCell.textContent?.trim() ?? ''}`;
                titleCell.append(slug);
                slugCell.classList.add('page-grid-index__slug-column');
                slugCell.setAttribute('aria-hidden', 'true');
            }

            if (statusCell) {
                const status = statusCell.textContent?.trim().toLowerCase() ?? '';
                statusCell.textContent = '';
                const pill = document.createElement('span');
                pill.className = `page-grid-index__status page-grid-index__status--${status}`;
                pill.textContent = status || 'unknown';
                statusCell.append(pill);
            }

            if (siteCell) {
                siteCell.classList.add('page-grid-index__site');
                const dot = document.createElement('span');
                dot.className = 'page-grid-index__site-dot';
                dot.setAttribute('aria-hidden', 'true');
                siteCell.prepend(dot);
            }

            if (actionsCell) {
                actionsCell.classList.add('page-grid-index__row-actions');
                actionsCell.childNodes.forEach((node) => {
                    if (node.nodeType === Node.TEXT_NODE) node.textContent = ' ';
                });
                actionsCell.querySelectorAll('a').forEach((action, index) => {
                    action.classList.add('page-grid-index__row-action');
                    if (index > 0) action.classList.add('page-grid-index__row-action--secondary');
                });
            }
        });

        if (table && paginationControls) {
            let footer = page.querySelector('[data-page-grid-pagination]');

            if (!footer) {
                footer = document.createElement('nav');
                footer.className = 'page-grid-index__pagination';
                footer.dataset.pageGridPagination = '';
                footer.setAttribute('aria-label', 'Page navigation');
                table.insertAdjacentElement('afterend', footer);
            }

            if (previous) {
                previous.classList.add('page-grid-index__previous');
                footer.append(previous);
            } else {
                const disabledPrevious = document.createElement('span');
                disabledPrevious.className = 'page-grid-index__previous is-disabled';
                disabledPrevious.setAttribute('aria-disabled', 'true');
                disabledPrevious.textContent = '« Previous';
                footer.append(disabledPrevious);
            }

            footer.append(paginationControls);

            if (next) {
                next.classList.add('page-grid-index__next');
                footer.append(next);
            } else {
                const disabledNext = document.createElement('span');
                disabledNext.className = 'page-grid-index__next is-disabled';
                disabledNext.setAttribute('aria-disabled', 'true');
                disabledNext.textContent = 'Next »';
                footer.append(disabledNext);
            }

            if (navigation && navigation !== footer) {
                navigation.remove();
            }

            Array.from(page.children).forEach((candidate) => {
                if (
                    candidate === footer
                    || !footer.compareDocumentPosition(candidate)
                    || !(footer.compareDocumentPosition(candidate)
                        & Node.DOCUMENT_POSITION_FOLLOWING)
                ) {
                    return;
                }

                const text = (candidate.textContent ?? '')
                    .replace(/\s+/g, ' ')
                    .trim()
                    .replace(/[«»]/g, '')
                    .trim();

                const paginationOnly = /^(Previous\s*)?(Next)?$/i.test(text)
                    && /Previous|Next/i.test(text);

                const containsFunctionalContent = candidate.querySelector(
                    'form, input, select, button, table, [data-grid-workspace], '
                    + '[data-page-grid-pagination]',
                );

                if (paginationOnly && !containsFunctionalContent) {
                    candidate.remove();
                }
            });
        }
    };

    document.querySelectorAll('.page-grid-index').forEach((page) => {
        initialise(page);

        Array.from(document.querySelectorAll('body *')).forEach((candidate) => {
            if (
                candidate.closest('.page-grid-index')
                || candidate.closest('aside')
                || candidate.closest('nav')
                || candidate.children.length > 0
                || candidate.textContent?.trim() !== 'Pages'
            ) {
                return;
            }

            const bounds = candidate.getBoundingClientRect();

            if (bounds.top < 80 && bounds.left >= 240) {
                candidate.hidden = true;
                candidate.dataset.pageGridDuplicateShellTitle = '';
            }
        });
    });
})();
