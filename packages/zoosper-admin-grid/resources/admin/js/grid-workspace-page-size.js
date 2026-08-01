(() => {
    'use strict';

    document.querySelectorAll('[data-grid-page-size]').forEach((select) => {
        select.addEventListener('change', () => {
            const form = select.closest('form');
            if (!form) return;

            const page = form.querySelector('input[name="page"]');
            if (page) page.value = '1';

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    });
})();
