(() => {
    'use strict';

    const initialise = (page) => {
        if (page.dataset.adminUsersEnhanced === 'true') return;
        const collection = page.querySelector('[data-admin-users-collection]');
        const workspace = collection?.querySelector('[data-grid-workspace]');
        const toolbar = workspace?.querySelector('.grid-compact-actions');
        const form = workspace?.querySelector('[data-grid-filter-form]');
        const query = form?.querySelector('[name="q"]');
        const sourceLabel = query?.closest('label');
        const table = collection?.querySelector('.grid-table');
        if (!collection || !workspace || !toolbar || !form || !query || !sourceLabel || !table) return;
        page.dataset.adminUsersEnhanced = 'true';
        if (!form.id) form.id = 'admin-users-grid-filter-form';

        const shellTitle = document.querySelector('.admin-topbar__title');
        if (shellTitle?.textContent?.trim() === 'Admin Users') shellTitle.hidden = true;

        const actions = page.querySelector('[data-admin-users-actions]');
        const create = Array.from(collection.querySelectorAll('a')).find((link) => link.textContent?.trim() === 'Create admin user');
        if (actions && create) {
            create.classList.add('admin-users-index__create');
            actions.append(create);
        }
        Array.from(collection.querySelectorAll('h1, h2')).forEach((heading) => {
            if (heading.textContent?.trim() === 'Admin Users') heading.hidden = true;
        });

        const search = document.createElement('label');
        search.className = 'admin-users-search';
        const accessible = document.createElement('span');
        accessible.className = 'sr-only';
        accessible.textContent = 'Search admin users';
        query.type = 'search';
        query.placeholder = 'Search users';
        query.setAttribute('aria-label', 'Search admin users');
        query.setAttribute('autocomplete', 'off');
        query.setAttribute('form', form.id);
        search.append(accessible, query);
        toolbar.prepend(search);
        sourceLabel.hidden = true;

        workspace.classList.add('admin-users-index__workspace');
        table.classList.add('admin-users-index__table');
        const summary = Array.from(collection.children).find((element) => /^Showing\s/i.test(element.textContent?.trim() ?? ''));
        summary?.classList.add('admin-users-index__summary');
        const pagination = collection.querySelector('.grid-pagination');
        if (pagination) {
            pagination.classList.add('admin-users-index__pagination');
            pagination.setAttribute('aria-label', 'Admin Users pagination');
        }

        table.querySelectorAll('tbody tr').forEach((row) => {
            if (row.dataset.adminUserRowEnhanced === 'true') return;
            row.dataset.adminUserRowEnhanced = 'true';
            const name = row.querySelector('td[data-grid-column="name"]');
            const email = row.querySelector('td[data-grid-column="email"]');
            const status = row.querySelector('td[data-grid-column="status"]');
            const actionsCell = row.querySelector('td[data-grid-column="actions"]');
            name?.classList.add('admin-users-index__name');
            email?.classList.add('admin-users-index__email');
            actionsCell?.classList.add('admin-users-index__row-actions');
            [name, email].forEach((cell) => {
                const value = cell?.textContent?.trim();
                if (cell && value) cell.title = value;
            });
            if (status) {
                const value = status.textContent?.trim() ?? '';
                const badge = document.createElement('span');
                badge.className = `admin-users-index__status admin-users-index__status--${value}`;
                badge.textContent = value;
                status.textContent = '';
                status.append(badge);
            }
        });

        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const pageInput = form.querySelector('[name="page"]');
            if (pageInput) pageInput.value = '1';
            form.requestSubmit();
        });
    };

    document.querySelectorAll('[data-admin-users-index]').forEach(initialise);
})();
