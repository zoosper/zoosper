(() => {
    'use strict';

    const bind = () => {
        const manifestNode = document.querySelector('[data-grid-bulk-action-manifest]');
        const action = document.querySelector('[data-grid-selection-bar] select[aria-label="Bulk actions"]');
        const table = document.querySelector('table.grid-has-selection');
        if (!manifestNode || !action || !table || action.dataset.gridServerMutationBound === 'true') return;

        let manifest;
        try { manifest = JSON.parse(manifestNode.textContent || '{}'); } catch { return; }
        const definition = Array.isArray(manifest.actions)
            ? manifest.actions.find((item) => item?.id === 'page.publish' && item?.executionType === 'server_mutation')
            : null;
        if (!definition) return;

        action.dataset.gridServerMutationBound = 'true';
        action.addEventListener('change', () => {
            if (action.value !== definition.id) return;

            const selected = Array.from(table.querySelectorAll('tbody input[name="selected_ids[]"]:checked'))
                .map((checkbox) => checkbox.value.trim())
                .filter(Boolean);
            const maximum = Number(definition.maximumSelection || 100);
            if (selected.length === 0 || selected.length > maximum) {
                window.alert(selected.length === 0 ? 'Select at least one Page.' : `Select no more than ${maximum} Pages.`);
                action.value = '';
                return;
            }
            if (!window.confirm(`Publish ${selected.length} selected ${selected.length === 1 ? 'Page' : 'Pages'}?`)) {
                action.value = '';
                return;
            }

            const token = manifestNode.dataset.csrfToken || '';
            const endpoint = manifestNode.dataset.serverAction || '';
            if (!token || !endpoint) {
                window.alert('Publish selected is unavailable because its protected endpoint is incomplete.');
                action.value = '';
                return;
            }

            const form = document.createElement('form');
            form.method = 'post';
            form.action = endpoint;
            form.hidden = true;
            const add = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = name; input.value = value; form.append(input);
            };
            add('_csrf_token', token);
            add('bulk_action', definition.id);
            add('confirmed_action', definition.id);
            selected.forEach((identity) => add('selected_ids[]', identity));
            document.body.append(form);
            form.submit();
        });
    };

    bind();
    new MutationObserver(bind).observe(document.body, {childList: true, subtree: true});
})();
