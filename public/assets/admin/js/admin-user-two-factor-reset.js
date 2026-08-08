(() => {
    'use strict';

    document.addEventListener('submit', (event) => {
        const submitter = event.submitter;
        if (!(submitter instanceof HTMLButtonElement)) {
            return;
        }

        const message = submitter.dataset.confirmMessage;
        if (typeof message === 'string' && message !== '' && !window.confirm(message)) {
            event.preventDefault();
        }
    });
})();
