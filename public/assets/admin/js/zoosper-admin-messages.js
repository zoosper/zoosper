(function () {
    'use strict';

    function removeDuplicateMessages(container) {
        var seen = new Set();
        container.querySelectorAll('[data-message-key]').forEach(function (message) {
            var key = message.getAttribute('data-message-key') || '';
            if (key !== '' && seen.has(key)) {
                message.remove();
                return;
            }
            seen.add(key);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.admin-flash-messages').forEach(function (container) {
            removeDuplicateMessages(container);
            container.addEventListener('click', function (event) {
                var target = event.target;
                if (target instanceof HTMLElement && target.classList.contains('admin-flash-message__dismiss')) {
                    var message = target.closest('.admin-flash-message');
                    if (message) {
                        message.remove();
                    }
                }
            });
        });
    });
}());
