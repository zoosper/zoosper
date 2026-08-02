(() => {
    'use strict';

    const ROOT_SELECTOR = '[data-permission-tree], .permission-tree';

    const normalise = (value) => String(value || '').trim().toLocaleLowerCase();

    const boot = () => {
        document.querySelectorAll(ROOT_SELECTOR).forEach((tree) => {
            if (tree.dataset.permissionExplorerBound === 'true') return;

            const checkboxes = Array.from(tree.querySelectorAll('input[type="checkbox"][name="permission_ids[]"]'));
            if (checkboxes.length === 0) return;

            tree.dataset.permissionExplorerBound = 'true';
            tree.classList.add('permission-explorer');

            const toolbar = document.createElement('div');
            toolbar.className = 'permission-explorer__toolbar';
            toolbar.innerHTML = [
                '<label class="permission-explorer__search-label">',
                '<span>Search permissions</span>',
                '<input type="search" class="permission-explorer__search" placeholder="Code or permission name" autocomplete="off">',
                '</label>',
                '<div class="permission-explorer__actions">',
                '<button type="button" class="permission-explorer__button" data-action="expand">Expand all</button>',
                '<button type="button" class="permission-explorer__button" data-action="collapse">Collapse all</button>',
                '<button type="button" class="permission-explorer__button" data-action="select-visible">Select visible</button>',
                '<button type="button" class="permission-explorer__button" data-action="clear-visible">Clear visible</button>',
                '</div>',
                '<output class="permission-explorer__count" aria-live="polite"></output>'
            ].join('');
            tree.prepend(toolbar);

            const search = toolbar.querySelector('.permission-explorer__search');
            const count = toolbar.querySelector('.permission-explorer__count');
            const rows = checkboxes.map((checkbox) => checkbox.closest('label, li, .permission-item, .form-check') || checkbox.parentElement);
            const groups = Array.from(tree.querySelectorAll('fieldset, details, .permission-group'));

            groups.forEach((group) => {
                if (group.matches('details')) return;
                const heading = group.querySelector(':scope > legend, :scope > h2, :scope > h3, :scope > h4, :scope > .permission-group__title');
                if (!heading || heading.querySelector('[data-permission-toggle]')) return;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'permission-explorer__group-toggle';
                button.dataset.permissionToggle = 'true';
                button.setAttribute('aria-expanded', 'true');
                button.textContent = 'Collapse';
                heading.append(' ', button);
                button.addEventListener('click', () => {
                    const collapsed = group.classList.toggle('permission-explorer__group--collapsed');
                    button.setAttribute('aria-expanded', String(!collapsed));
                    button.textContent = collapsed ? 'Expand' : 'Collapse';
                });
            });

            const visibleRows = () => rows.filter((row) => row && !row.hidden);
            const refreshCount = () => {
                const selected = checkboxes.filter((checkbox) => checkbox.checked).length;
                count.value = `${selected} of ${checkboxes.length} selected`;
                count.textContent = count.value;
            };
            const applySearch = () => {
                const needle = normalise(search.value);
                rows.forEach((row) => {
                    if (!row) return;
                    row.hidden = needle !== '' && !normalise(row.textContent).includes(needle);
                });
                groups.forEach((group) => {
                    const candidates = rows.filter((row) => row && group.contains(row));
                    group.hidden = candidates.length > 0 && candidates.every((row) => row.hidden);
                });
            };

            search.addEventListener('input', applySearch);
            checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshCount));
            toolbar.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const action = button.dataset.action;
                if (action === 'expand' || action === 'collapse') {
                    const collapse = action === 'collapse';
                    groups.forEach((group) => {
                        if (group.matches('details')) group.open = !collapse;
                        group.classList.toggle('permission-explorer__group--collapsed', collapse);
                        const toggle = group.querySelector('[data-permission-toggle]');
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', String(!collapse));
                            toggle.textContent = collapse ? 'Expand' : 'Collapse';
                        }
                    });
                    return;
                }
                const checked = action === 'select-visible';
                visibleRows().forEach((row) => {
                    const checkbox = row.querySelector('input[type="checkbox"][name="permission_ids[]"]');
                    if (checkbox && !checkbox.disabled) checkbox.checked = checked;
                });
                refreshCount();
            });
            refreshCount();
        });
    };

    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
