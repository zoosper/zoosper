(() => {
    'use strict';

    const initialise = (page) => {
        if (page.dataset.rolesEnhanced === 'true') return;
        const collection = page.querySelector('[data-roles-collection]');
        const workspace = collection?.querySelector('[data-grid-workspace]');
        const toolbar = workspace?.querySelector('.grid-compact-actions');
        const form = workspace?.querySelector('[data-grid-filter-form]');
        const query = form?.querySelector('[name="q"]');
        const sourceLabel = query?.closest('label');
        const table = collection?.querySelector('.grid-table');
        if (!collection || !workspace || !toolbar || !form || !query || !sourceLabel || !table) return;
        page.dataset.rolesEnhanced = 'true';
        if (!form.id) form.id = 'roles-grid-filter-form';

        const actions = page.querySelector('[data-roles-actions]');
        const create = Array.from(collection.querySelectorAll('a')).find((link) => link.textContent?.trim() === 'Create role');
        if (actions && create) {
            create.classList.add('roles-index__create');
            actions.append(create);
        }
        Array.from(collection.querySelectorAll('h1, h2')).forEach((heading) => {
            if (heading.textContent?.trim() === 'Roles & Permissions') heading.hidden = true;
        });

        const search = document.createElement('label');
        search.className = 'roles-search';
        const accessible = document.createElement('span');
        accessible.className = 'sr-only';
        accessible.textContent = 'Search roles';
        query.type = 'search';
        query.placeholder = 'Search roles';
        query.setAttribute('aria-label', 'Search roles');
        query.setAttribute('autocomplete', 'off');
        query.setAttribute('form', form.id);
        search.append(accessible, query);
        toolbar.prepend(search);
        sourceLabel.hidden = true;

        workspace.classList.add('roles-index__workspace');
        table.classList.add('roles-index__table');
        const pagination = collection.querySelector('.grid-pagination');
        if (pagination) {
            pagination.classList.add('roles-index__pagination');
            pagination.setAttribute('aria-label', 'Roles pagination');
        }
        table.querySelectorAll('tbody tr').forEach((row) => {
            const label = row.querySelector('td[data-grid-column="label"]');
            const code = row.querySelector('td[data-grid-column="code"]');
            const action = row.querySelector('td[data-grid-column="actions"]');
            label?.classList.add('roles-index__label');
            code?.classList.add('roles-index__code');
            action?.classList.add('roles-index__row-actions');
            [label, code].forEach((cell) => {
                const value = cell?.textContent?.trim();
                if (cell && value) cell.title = value;
            });
        });

        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const pageInput = form.querySelector('[name="page"]');
            if (pageInput) pageInput.value = '1';
            form.requestSubmit();
        });
    };

    document.querySelectorAll('[data-roles-index]').forEach(initialise);
})();
