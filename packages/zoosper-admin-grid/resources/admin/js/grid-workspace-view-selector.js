(() => {
    'use strict';
    const boot = () => document.querySelectorAll('[data-grid-view-selector]').forEach((selector) => {
        if (selector.dataset.gridViewSelectorBound === 'true') return;
        selector.dataset.gridViewSelectorBound = 'true';
        selector.addEventListener('change', () => {
            const target = String(selector.value || '');
            if (target.startsWith('/') && !target.startsWith('//')) window.location.assign(target);
        });
    });
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
