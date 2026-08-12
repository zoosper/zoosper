(() => {
    'use strict';

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('[data-page-revision-history] .page-revision-pagination a');
        if (!link) {
            return;
        }

        const details = link.closest('[data-page-revision-history]');
        const results = details?.querySelector('[data-page-revision-results]');
        if (!details || !results || typeof window.fetch !== 'function') {
            return;
        }

        event.preventDefault();
        if (details.dataset.loading === '1') {
            return;
        }

        details.dataset.loading = '1';
        results.setAttribute('aria-busy', 'true');
        link.setAttribute('aria-disabled', 'true');

        try {
            const response = await window.fetch(link.href, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
            });
            if (!response.ok) {
                throw new Error(`Revision history request failed with ${response.status}.`);
            }

            results.innerHTML = await response.text();
            details.open = true;
            const url = new URL(link.href, window.location.href);
            const editUrl = new URL(window.location.href);
            editUrl.searchParams.set('revision_page', url.searchParams.get('revision_page') || '1');
            editUrl.hash = 'revision-history';
            window.history.replaceState({}, '', editUrl);
            results.querySelector('.page-revision-pagination')?.focus?.();
        } catch (error) {
            window.location.assign(link.href);
        } finally {
            delete details.dataset.loading;
            results.removeAttribute('aria-busy');
            link.removeAttribute('aria-disabled');
        }
    });
})();
