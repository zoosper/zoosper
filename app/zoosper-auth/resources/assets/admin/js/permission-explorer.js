(() => {
    'use strict';

    const treeSelector = '[data-permission-tree], .permission-tree';
    const checkboxSelector = 'input[type="checkbox"][name="permission_ids[]"]';
    const groupSelector = 'fieldset, details, .permission-group';
    const normalise = (value) => String(value || '').trim().toLocaleLowerCase();

    const boot = () => document.querySelectorAll(treeSelector).forEach((tree) => {
        if (tree.dataset.permissionExplorerBound === 'true') return;
        const checkboxes = Array.from(tree.querySelectorAll(checkboxSelector));
        if (checkboxes.length === 0) return;

        tree.dataset.permissionExplorerBound = 'true';
        tree.classList.add('permission-explorer');
        const rows = checkboxes.map((checkbox) => checkbox.closest('label, li, .permission-item, .form-check') || checkbox.parentElement);
        const groups = Array.from(tree.querySelectorAll(groupSelector));
        const toolbar = document.createElement('div');
        toolbar.className = 'permission-explorer__toolbar';
        toolbar.innerHTML = '<label class="permission-explorer__search-label"><span>Search permissions</span><input type="search" class="permission-explorer__search" placeholder="Code or permission name" autocomplete="off"></label><div class="permission-explorer__actions"><button type="button" data-action="expand">Expand all</button><button type="button" data-action="collapse">Collapse all</button><button type="button" data-action="select-visible">Select visible</button><button type="button" data-action="clear-visible">Clear visible</button></div><output class="permission-explorer__count" aria-live="polite"></output>';
        tree.prepend(toolbar);
        const search = toolbar.querySelector('.permission-explorer__search');
        const count = toolbar.querySelector('.permission-explorer__count');
        const groupRows = (group) => rows.filter((row) => row && group.contains(row));
        const setCollapsed = (group, collapsed) => {
            if (group.matches('details')) group.open = !collapsed;
            group.classList.toggle('permission-explorer__group--collapsed', collapsed);
            const toggle = group.querySelector('[data-group-toggle]');
            if (toggle) {
                toggle.setAttribute('aria-expanded', String(!collapsed));
                toggle.textContent = collapsed ? 'Expand' : 'Collapse';
            }
        };
        groups.forEach((group) => {
            if (group.matches('details')) return;
            const heading = group.querySelector(':scope > legend, :scope > h2, :scope > h3, :scope > h4, :scope > .permission-group__title');
            if (!heading || heading.querySelector('[data-group-toggle]')) return;
            heading.classList.add('permission-explorer__heading');
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.groupToggle = 'true';
            button.className = 'permission-explorer__group-toggle';
            button.setAttribute('aria-expanded', 'true');
            button.textContent = 'Collapse';
            heading.append(' ', button);
            button.addEventListener('click', () => setCollapsed(group, !group.classList.contains('permission-explorer__group--collapsed')));
        });
        const refreshCount = () => count.textContent = `${checkboxes.filter((checkbox) => checkbox.checked).length} of ${checkboxes.length} selected`;
        const applySearch = () => {
            const needle = normalise(search.value);
            rows.forEach((row) => { if (row) row.hidden = needle !== '' && !normalise(row.textContent).includes(needle); });
            groups.forEach((group) => {
                const candidates = groupRows(group);
                const visible = candidates.some((row) => !row.hidden);
                group.hidden = candidates.length > 0 && !visible;
                if (needle !== '' && visible) setCollapsed(group, false);
            });
        };
        search.addEventListener('input', applySearch);
        search.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && search.value !== '') { search.value = ''; applySearch(); }
        });
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshCount));
        toolbar.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button) return;
            if (button.dataset.action === 'expand' || button.dataset.action === 'collapse') {
                groups.filter((group) => !group.hidden).forEach((group) => setCollapsed(group, button.dataset.action === 'collapse'));
                return;
            }
            const checked = button.dataset.action === 'select-visible';
            rows.filter((row) => row && !row.hidden).forEach((row) => {
                const checkbox = row.querySelector(checkboxSelector);
                if (checkbox && !checkbox.disabled) checkbox.checked = checked;
            });
            refreshCount();
        });
        refreshCount();
    });

    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
