(() => {
    'use strict';
    const forms = {
        save_view: 'grid-workspace-save-view',
        set_default_view: 'grid-workspace-set-default-view',
        delete_view: 'grid-workspace-delete-view'
    };

    document.querySelectorAll('[data-grid-workspace]').forEach((workspace) => {
        const visibleName = workspace.querySelector('[data-grid-view-name]');
        workspace.querySelectorAll('[data-grid-view-action]').forEach((button) => {
            const action = button.dataset.gridViewAction || '';
            const formId = forms[action];
            const form = formId ? document.getElementById(formId) : null;
            if (!form) {
                button.disabled = true;
                return;
            }
            button.setAttribute('form', formId);
            button.addEventListener('click', (event) => {
                if (action === 'delete_view') return;
                const canonical = form.querySelector('input[name="view_name"]');
                const value = visibleName ? visibleName.value.trim() : '';
                if (!canonical || value === '') {
                    event.preventDefault();
                    if (visibleName) {
                        visibleName.setCustomValidity('Enter a view name.');
                        visibleName.reportValidity();
                        visibleName.focus();
                    }
                    return;
                }
                visibleName.setCustomValidity('');
                canonical.value = value;
            });
            if (visibleName) visibleName.addEventListener('input', () => visibleName.setCustomValidity(''));
        });
    });
})();
