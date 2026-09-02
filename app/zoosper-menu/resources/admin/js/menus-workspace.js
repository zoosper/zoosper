(() => {
    'use strict';
    const initialise = (page) => {
        if (page.dataset.menusEnhanced === 'true') return;
        const workspace = page.querySelector('[data-grid-workspace]');
        const toolbar = workspace?.querySelector('.grid-compact-actions');
        const form = workspace?.querySelector('[data-grid-filter-form]');
        const query = form?.querySelector('[name="q"]');
        const sourceLabel = query?.closest('label');
        const table = page.querySelector('.grid-table');
        const controls = page.querySelector('.grid-pagination-controls');
        if (!workspace || !toolbar || !form || !query || !sourceLabel || !table) return;
        page.dataset.menusEnhanced = 'true';
        if (!form.id) form.id = 'menus-grid-filter-form';
        const search = document.createElement('label');
        search.className = 'menus-search';
        const accessible = document.createElement('span');
        accessible.className = 'sr-only'; accessible.textContent = 'Search menus';
        query.type = 'search'; query.placeholder = 'Search menus'; query.setAttribute('aria-label', 'Search menus'); query.setAttribute('autocomplete', 'off'); query.setAttribute('form', form.id);
        search.append(accessible, query); toolbar.prepend(search); sourceLabel.hidden = true;
        const summary = Array.from(page.children).find((element) => element !== workspace && element !== table && /^Showing\s/i.test(element.textContent?.trim() ?? ''));
        const legacy = page.querySelector('.grid-workspace__navigation');
        const previous = page.querySelector('[rel="prev"]'); const next = page.querySelector('[rel="next"]');
        workspace.classList.add('menus-index__workspace'); table.classList.add('menus-index__table'); summary?.classList.add('menus-index__summary');
        if (controls) {
            const footer = document.createElement('nav'); footer.className = 'menus-index__pagination'; footer.dataset.menusPagination = ''; footer.setAttribute('aria-label', 'Menus pagination');
            const previousControl = previous ?? document.createElement('span');
            if (!previous) { previousControl.textContent = '« Previous'; previousControl.setAttribute('aria-disabled', 'true'); previousControl.classList.add('is-disabled'); }
            previousControl.classList.add('menus-index__previous');
            const nextControl = next ?? document.createElement('span');
            if (!next) { nextControl.textContent = 'Next »'; nextControl.setAttribute('aria-disabled', 'true'); nextControl.classList.add('is-disabled'); }
            nextControl.classList.add('menus-index__next');
            footer.append(previousControl, controls, nextControl); table.insertAdjacentElement('afterend', footer); legacy?.remove();
            Array.from(page.children).forEach((candidate) => {
                if (candidate === footer || !(footer.compareDocumentPosition(candidate) & Node.DOCUMENT_POSITION_FOLLOWING)) return;
                const text = (candidate.textContent ?? '').replace(/\s+/g, ' ').replace(/[«»]/g, '').trim();
                const paginationOnly = /^(Previous\s*)?(Next)?$/i.test(text) && /Previous|Next/i.test(text);
                const functional = candidate.querySelector('form, input, select, button, table, [data-grid-workspace], [data-menus-pagination]');
                if (paginationOnly && !functional) candidate.remove();
            });
        }
        table.querySelectorAll('tbody tr').forEach((row) => {
            if (row.dataset.menuRowEnhanced === 'true') return; row.dataset.menuRowEnhanced = 'true';
            const label = row.querySelector('td[data-grid-column="label"]'); const code = row.querySelector('td[data-grid-column="code"]'); const site = row.querySelector('td[data-grid-column="site_name"]'); const status = row.querySelector('td[data-grid-column="status"]'); const updated = row.querySelector('td[data-grid-column="updated_at"]'); const actions = row.querySelector('td[data-grid-column="actions"]');
            label?.classList.add('menus-index__label'); code?.classList.add('menus-index__code'); site?.classList.add('menus-index__site'); updated?.classList.add('menus-index__updated'); actions?.classList.add('menus-index__actions');
            [label, code, site, updated].forEach((cell) => { const value = cell?.textContent?.trim(); if (cell && value) cell.title = value; });
            if (status) { const value = status.textContent?.trim() ?? ''; const badge = document.createElement('span'); badge.className = `menus-index__status menus-index__status--${value}`; badge.textContent = value; status.textContent = ''; status.append(badge); }
        });
        const shellTitle = document.querySelector('.admin-topbar__title'); if (shellTitle?.textContent?.trim() === 'Menus') shellTitle.hidden = true;
        search.addEventListener('keydown', (event) => { if (event.key !== 'Enter') return; event.preventDefault(); const pageInput = form.querySelector('[name="page"]'); if (pageInput) pageInput.value = '1'; form.requestSubmit(); });
    };
    document.querySelectorAll('.menus-index').forEach(initialise);
})();
