(() => {
    'use strict';

    const workspace = document.querySelector('[data-grid-workspace]');
    if (!workspace) return;

    const pagePattern = /Page\s+(\d+)\s+of\s+(\d+)/i;
    const walker = document.createTreeWalker(
        document.body,
        NodeFilter.SHOW_TEXT,
        {
            acceptNode(node) {
                if (!node.parentElement || node.parentElement.closest('[data-grid-page-jump]')) {
                    return NodeFilter.FILTER_REJECT;
                }
                return pagePattern.test(node.textContent ?? '')
                    ? NodeFilter.FILTER_ACCEPT
                    : NodeFilter.FILTER_REJECT;
            },
        },
    );
    const pageText = walker.nextNode();
    if (!pageText?.parentElement) return;

    const match = (pageText.textContent ?? '').match(pagePattern);
    if (!match) return;
    const currentPage = Number.parseInt(match[1], 10);
    const totalPages = Number.parseInt(match[2], 10);
    if (!Number.isInteger(currentPage) || !Number.isInteger(totalPages) || totalPages < 1) return;

    const host = pageText.parentElement;
    const form = document.createElement('form');
    form.method = 'get';
    form.action = window.location.pathname;
    form.className = 'grid-page-jump';
    form.dataset.gridPageJump = '';
    form.setAttribute('aria-label', 'Jump to Grid page');

    const params = new URLSearchParams(window.location.search);
    params.delete('page');
    for (const [name, value] of params.entries()) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = name;
        hidden.value = value;
        form.append(hidden);
    }

    const label = document.createElement('label');
    label.textContent = 'Page ';
    const input = document.createElement('input');
    input.type = 'number';
    input.name = 'page';
    input.value = String(currentPage);
    input.min = '1';
    input.max = String(totalPages);
    input.inputMode = 'numeric';
    input.required = true;
    input.setAttribute('aria-label', `Current page, enter 1 to ${totalPages}`);
    label.append(input, document.createTextNode(` of ${totalPages}`));

    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = 'Go';
    form.append(label, submit);

    form.addEventListener('submit', (event) => {
        const requested = Number.parseInt(input.value, 10);
        if (!Number.isInteger(requested) || requested < 1 || requested > totalPages) {
            event.preventDefault();
            input.setCustomValidity(`Enter a page from 1 to ${totalPages}.`);
            input.reportValidity();
            return;
        }
        input.setCustomValidity('');
    });
    input.addEventListener('input', () => input.setCustomValidity(''));

    pageText.textContent = (pageText.textContent ?? '').replace(pagePattern, '');
    host.classList.add('grid-pagination-controls');
    host.insertBefore(form, pageText.nextSibling);

    const pageSize = workspace.querySelector('[data-grid-page-size]');
    const pageSizeLabel = pageSize?.closest('label');
    if (pageSizeLabel) {
        host.insertBefore(pageSizeLabel, form);
        workspace.classList.add('grid-page-size-relocated');
    }
})();
