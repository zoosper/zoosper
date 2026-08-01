(() => {
    'use strict';

    document.querySelectorAll('[data-grid-workspace]').forEach((root) => {
        const panel = (name) => root.querySelector(`[data-grid-panel="${name}"]`);

        root.querySelectorAll('[data-grid-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = panel(button.dataset.gridToggle);
                if (!target) return;
                const open = target.hidden;
                target.hidden = !open;
                button.setAttribute('aria-expanded', String(open));
            });
        });

        root.querySelectorAll('[data-grid-panel-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.closest('[data-grid-panel]');
                if (!target) return;
                target.hidden = true;
                root.querySelector(`[data-grid-toggle="${target.dataset.gridPanel}"]`)
                    ?.setAttribute('aria-expanded', 'false');
            });
        });

        root.querySelectorAll('[data-grid-remove-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                const name = button.dataset.gridRemoveFilter;
                root.querySelectorAll(`[name="${CSS.escape(name)}"], [name="${CSS.escape(name)}[]"]`)
                    .forEach((input) => {
                        if (input instanceof HTMLSelectElement) {
                            Array.from(input.options).forEach((option) => option.selected = false);
                        } else {
                            input.value = '';
                        }
                    });
                root.querySelector('[data-grid-filter-form]')?.requestSubmit();
            });
        });

        root.querySelectorAll('[data-grid-page-size]').forEach((select) => {
            select.addEventListener('change', () => {
                const form = root.querySelector('[data-grid-filter-form]');
                if (!form) return;
                const page = form.querySelector('[name="page"]');
                if (page) page.value = '1';
                let value = form.querySelector('[name="page_size"]');
                if (!value) {
                    value = document.createElement('input');
                    value.type = 'hidden';
                    value.name = 'page_size';
                    form.append(value);
                }
                value.value = select.value;
                form.requestSubmit();
            });
        });
    });
})();
