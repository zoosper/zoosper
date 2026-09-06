(() => {
    'use strict';

    const page = document.querySelector('[data-email-logs-page]');
    if (!page || page.dataset.emailLogsEnhanced === 'true') return;

    const workspace = page.querySelector('[data-grid-workspace]');
    const table = page.querySelector('.grid-table');
    const controls = page.querySelector('.grid-pagination-controls');
    if (!workspace || !table || !controls) return;

    page.dataset.emailLogsEnhanced = 'true';
    workspace.classList.add('email-logs-index__workspace');
    table.classList.add('email-logs-index__table');

    const previous = page.querySelector('[rel="prev"]');
    const next = page.querySelector('[rel="next"]');
    const legacy = page.querySelector('.grid-pagination');
    const footer = document.createElement('nav');
    footer.className = 'email-logs-index__pagination';
    footer.dataset.emailLogsPagination = '';
    footer.setAttribute('aria-label', 'Email Logs pagination');

    const previousControl = previous ?? document.createElement('span');
    if (!previous) {
        previousControl.textContent = 'Previous';
        previousControl.setAttribute('aria-disabled', 'true');
        previousControl.classList.add('is-disabled');
    }
    previousControl.classList.add('email-logs-index__previous');

    const nextControl = next ?? document.createElement('span');
    if (!next) {
        nextControl.textContent = 'Next';
        nextControl.setAttribute('aria-disabled', 'true');
        nextControl.classList.add('is-disabled');
    }
    nextControl.classList.add('email-logs-index__next');

    footer.append(previousControl, controls, nextControl);
    table.insertAdjacentElement('afterend', footer);
    if (legacy && legacy !== footer) legacy.remove();

    Array.from(page.children).forEach((candidate) => {
        if (candidate === footer) return;
        const text = (candidate.textContent ?? '').replace(/\s+/g, ' ').trim();
        const paginationOnly = /^(Previous\s*)?(Next)?$/i.test(text)
            && /Previous|Next/i.test(text);
        const functional = candidate.querySelector(
            'form, input, select, button, table, [data-grid-workspace], '
            + '[data-email-logs-pagination]',
        );
        if (paginationOnly && !functional) candidate.remove();
    });
})();
