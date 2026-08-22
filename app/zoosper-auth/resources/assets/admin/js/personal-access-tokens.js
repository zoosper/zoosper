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
