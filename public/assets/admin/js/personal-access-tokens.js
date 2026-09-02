(() => {
    'use strict';

    const screens = document.querySelectorAll('[data-pat-screen]');

    screens.forEach((screen) => {
        const form = screen.querySelector('[data-pat-form]');
        const copyButton = screen.querySelector('[data-pat-copy]');
        const copyStatus = screen.querySelector('[data-pat-copy-status]');
        const focusLink = screen.querySelector('[data-pat-focus-create]');

        if (focusLink) {
            focusLink.addEventListener('click', () => {
                window.setTimeout(() => screen.querySelector('[data-pat-name]')?.focus(), 0);
            });
        }

        if (copyButton) {
            copyButton.addEventListener('click', async () => {
                const targetId = copyButton.dataset.copyTarget;
                const target = targetId ? document.getElementById(targetId) : null;
                if (!target) return;

                try {
                    if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
                    await navigator.clipboard.writeText(target.textContent || '');
                    copyButton.textContent = 'Copied';
                    if (copyStatus) copyStatus.textContent = 'Token copied to the clipboard.';
                } catch (_error) {
                    const selection = window.getSelection();
                    const range = document.createRange();
                    range.selectNodeContents(target);
                    selection?.removeAllRanges();
                    selection?.addRange(range);
                    if (copyStatus) copyStatus.textContent = 'Clipboard access is unavailable. The token has been selected for manual copying.';
                }
            });
        }

        if (!form) return;

        const checkboxes = Array.from(form.querySelectorAll('input[name="scopes[]"]'));
        const count = form.querySelector('[data-pat-scope-count]');
        const help = form.querySelector('[data-pat-selection-help]');
        const create = form.querySelector('[data-pat-create]');
        const groups = Array.from(form.querySelectorAll('[data-pat-scope-group]'));

        const refresh = () => {
            const selected = checkboxes.filter((checkbox) => checkbox.checked);
            const destructive = selected.filter((checkbox) => checkbox.closest('.pat-scope-chip--destructive'));
            if (count) count.textContent = `${selected.length} of ${checkboxes.length} selected`;
            if (help) {
                help.textContent = selected.length === 0
                    ? 'Pick at least one scope to continue.'
                    : `${selected.length} scope${selected.length === 1 ? '' : 's'} selected${destructive.length > 0 ? ` · ${destructive.length} destructive` : ''}.`;
            }
            if (create) create.disabled = selected.length === 0;

            groups.forEach((group) => {
                const inputs = Array.from(group.querySelectorAll('input[name="scopes[]"]'));
                const selectedInGroup = inputs.filter((checkbox) => checkbox.checked).length;
                group.dataset.hasSelection = selectedInGroup > 0 ? 'true' : 'false';
                const toggle = group.querySelector('[data-pat-select-group]');
                if (toggle) toggle.textContent = selectedInGroup === inputs.length ? 'Clear group' : 'Select group';
            });
        };

        form.addEventListener('change', (event) => {
            if (event.target instanceof HTMLInputElement && event.target.name === 'scopes[]') refresh();
        });

        form.querySelector('[data-pat-select-all]')?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => checkbox.checked = true);
            refresh();
        });

        form.querySelector('[data-pat-clear]')?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => checkbox.checked = false);
            refresh();
        });

        groups.forEach((group) => {
            group.querySelector('[data-pat-select-group]')?.addEventListener('click', () => {
                const inputs = Array.from(group.querySelectorAll('input[name="scopes[]"]'));
                const next = !inputs.every((checkbox) => checkbox.checked);
                inputs.forEach((checkbox) => checkbox.checked = next);
                refresh();
            });
        });

        refresh();
    });
})();

/* Phase 12H: connect the existing owner-scoped token Grid to the accepted collection surface. */
(() => {
    'use strict';

    const initialise = (screen) => {
        const shellTitle = document.querySelector('.admin-topbar__title');
        if (shellTitle?.textContent?.trim() === 'Personal Access Tokens') {
            shellTitle.hidden = true;
            shellTitle.dataset.patDuplicateShellTitle = '';
        }

        if (screen.dataset.patGridEnhanced === 'true') return;

        const list = screen.querySelector('.pat-token-list');
        const scroll = list?.querySelector('.pat-grid-scroll');
        const workspace = scroll?.querySelector('[data-grid-workspace]');
        const toolbar = workspace?.querySelector('.grid-compact-actions');
        const form = workspace?.querySelector('[data-grid-filter-form]');
        const query = form?.querySelector('[name="q"]');
        const sourceLabel = query?.closest('label');
        const table = scroll?.querySelector('.grid-table');
        const pagination = scroll?.querySelector('.grid-pagination');

        if (!list || !scroll || !workspace || !toolbar || !form || !query || !sourceLabel || !table) return;
        screen.dataset.patGridEnhanced = 'true';
        if (!form.id) form.id = 'pat-grid-filter-form';

        const search = document.createElement('label');
        search.className = 'pat-grid-search';
        const accessible = document.createElement('span');
        accessible.className = 'sr-only';
        accessible.textContent = 'Search tokens';
        query.type = 'search';
        query.placeholder = 'Search tokens';
        query.setAttribute('aria-label', 'Search tokens');
        query.setAttribute('autocomplete', 'off');
        query.setAttribute('form', form.id);
        search.append(accessible, query);
        toolbar.prepend(search);
        sourceLabel.hidden = true;

        workspace.querySelectorAll('[data-grid-export]').forEach((control) => {
            control.hidden = true;
            control.setAttribute('aria-hidden', 'true');
            control.setAttribute('tabindex', '-1');
        });

        const summary = Array.from(scroll.children).find((element) => {
            return element !== workspace && element !== table
                && /^Showing\s/i.test(element.textContent?.trim() ?? '');
        });
        const legacy = scroll.querySelector('.grid-workspace__navigation');
        const previous = scroll.querySelector('[rel="prev"]');
        const next = scroll.querySelector('[rel="next"]');

        workspace.classList.add('pat-token-list__workspace');
        table.classList.add('pat-token-list__table');
        summary?.classList.add('pat-token-list__summary');

        if (pagination) {
            pagination.classList.add('pat-token-list__pagination');
            pagination.dataset.patPagination = '';
            pagination.setAttribute('aria-label', 'Access Tokens pagination');
            pagination.querySelector('.grid-pagination__prev')?.classList.add('pat-token-list__previous');
            pagination.querySelector('.grid-pagination__status')?.classList.add('pat-token-list__status');
            pagination.querySelector('.grid-pagination__next')?.classList.add('pat-token-list__next');
        }

        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const page = form.querySelector('[name="page"]');
            if (page) page.value = '1';
            form.requestSubmit();
        });
    };

    document.querySelectorAll('[data-pat-screen]').forEach(initialise);
})();
