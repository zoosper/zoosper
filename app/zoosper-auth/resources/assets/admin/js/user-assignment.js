(() => {
    'use strict';

    const checkboxSelector = 'input[type="checkbox"][name="user_ids[]"]';
    const normalise = (value) => String(value || '').trim().toLocaleLowerCase();

    const boot = () => {
        document.querySelectorAll('[data-role-user-assignment]').forEach((container) => {
            if (container.dataset.bound === 'true') {
                return;
            }
            container.dataset.bound = 'true';

            const searchInput = container.querySelector('[data-role-user-search]');
            const countOutput = container.querySelector('[data-role-user-count]');
            const rows = Array.from(container.querySelectorAll('[data-role-user-item]'));
            const checkboxes = Array.from(container.querySelectorAll(checkboxSelector));

            const refreshCount = () => {
                if (!countOutput) {
                    return;
                }
                const checked = checkboxes.filter((cb) => cb.checked).length;
                countOutput.textContent = `${checked} of ${checkboxes.length} selected`;
            };

            const applySearch = () => {
                const needle = normalise(searchInput ? searchInput.value : '');
                rows.forEach((row) => {
                    const text = normalise(row.textContent);
                    row.hidden = needle !== '' && !text.includes(needle);
                });
            };

            if (searchInput) {
                searchInput.addEventListener('input', applySearch);
                searchInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && searchInput.value !== '') {
                        searchInput.value = '';
                        applySearch();
                    }
                });
            }

            checkboxes.forEach((cb) => {
                cb.addEventListener('change', () => {
                    const label = cb.closest('label');
                    if (label) {
                        label.classList.toggle('is-selected', cb.checked);
                    }
                    refreshCount();
                });
            });

            container.addEventListener('click', (event) => {
                const btn = event.target.closest('[data-role-user-action]');
                if (!btn) {
                    return;
                }
                const action = btn.dataset.roleUserAction;
                if (action === 'select-visible' || action === 'clear-visible') {
                    const check = action === 'select-visible';
                    rows.filter((r) => !r.hidden).forEach((r) => {
                        const cb = r.querySelector(checkboxSelector);
                        if (cb && !cb.disabled) {
                            cb.checked = check;
                            r.classList.toggle('is-selected', check);
                        }
                    });
                    refreshCount();
                }
            });

            refreshCount();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
