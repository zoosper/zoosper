(() => {
    'use strict';

    const checkboxSelector = 'input[type="checkbox"][name="permission_ids[]"]';
    const groupSelector = 'fieldset, details, .permission-group';
    const normalise = (value) => String(value || '').trim().toLocaleLowerCase();

    const discoverTrees = () => {
        const roots = Array.from(document.querySelectorAll('[data-permission-tree], .permission-tree'));
        document.querySelectorAll(checkboxSelector).forEach((checkbox) => {
            const form = checkbox.closest('form');
            if (form && !roots.includes(form)) roots.push(form);
        });
        return roots;
    };

    const boot = () => discoverTrees().forEach((tree) => {
        if (tree.dataset.permissionExplorerBound === 'true') return;
        const checkboxes = Array.from(tree.querySelectorAll(checkboxSelector));
        if (checkboxes.length === 0) return;

        tree.dataset.permissionExplorerBound = 'true';
        tree.classList.add('permission-explorer');
        const rows = checkboxes.map((checkbox) => checkbox.closest('label, li, .permission-item, .form-check') || checkbox.parentElement);
        const groups = Array.from(tree.querySelectorAll(groupSelector)).filter((group) => group.querySelector(checkboxSelector));
        const toolbar = document.createElement('div');
        toolbar.className = 'permission-explorer__toolbar';
        const searchLabel = document.createElement('label');
        searchLabel.className = 'permission-explorer__search-label';
        const searchText = document.createElement('span');
        searchText.textContent = 'Search permissions';
        const searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.className = 'permission-explorer__search';
        searchInput.placeholder = 'Code or permission name';
        searchInput.autocomplete = 'off';
        searchLabel.append(searchText, searchInput);
        const actions = document.createElement('div');
        actions.className = 'permission-explorer__actions';
        [['expand', 'Expand all'], ['collapse', 'Collapse all'], ['select-visible', 'Select visible'], ['clear-visible', 'Clear visible']].forEach(([action, label]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.action = action;
            button.textContent = label;
            actions.append(button);
        });
        const selectionCount = document.createElement('output');
        selectionCount.className = 'permission-explorer__count';
        selectionCount.setAttribute('aria-live', 'polite');
        toolbar.append(searchLabel, actions, selectionCount);
        const firstGroup = groups[0] || rows.find(Boolean);
        if (firstGroup) firstGroup.before(toolbar); else tree.prepend(toolbar);

        const search = searchInput;
        const count = selectionCount;
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
