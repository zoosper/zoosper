(function () {
    'use strict';

    function initAnnouncementModal() {
        var modal = document.getElementById('admin-announcement-modal');
        if (!modal) {
            return;
        }

        var dialog = modal.querySelector('.admin-announcement-dialog');
        var titleEl = document.getElementById('admin-announcement-title');
        var bodyEl = document.getElementById('admin-announcement-body');
        var form = modal.querySelector('[data-announcement-ack-form]');
        var idInput = modal.querySelector('[data-announcement-id-input]');
        var ackBtn = modal.querySelector('[data-announcement-ack-btn]');
        var closeBtn = modal.querySelector('[data-announcement-close-btn]');

        var previouslyFocusedElement = null;
        var acknowledgedIds = {};
        var isPolling = false;

        function showModal(id, title, body) {
            if (acknowledgedIds[id]) {
                return;
            }

            modal.setAttribute('data-announcement-id', id);
            if (idInput) {
                idInput.value = id;
            }
            if (titleEl) {
                titleEl.textContent = title;
            }
            if (bodyEl) {
                bodyEl.textContent = body;
            }

            modal.removeAttribute('hidden');
            modal.classList.add('is-visible');

            previouslyFocusedElement = document.activeElement;
            if (ackBtn) {
                ackBtn.focus();
            }
        }

        function hideModal() {
            modal.classList.remove('is-visible');
            modal.setAttribute('hidden', '');
            if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
                previouslyFocusedElement.focus();
            }
        }

        function handleAcknowledge(e) {
            if (e) {
                e.preventDefault();
            }

            var announcementId = modal.getAttribute('data-announcement-id') || (idInput ? idInput.value : '');
            if (!announcementId) {
                hideModal();
                return;
            }

            acknowledgedIds[announcementId] = true;
            hideModal();

            var csrfToken = '';
            var csrfInput = form ? form.querySelector('input[name="_csrf_token"]') : null;
            if (csrfInput) {
                csrfToken = csrfInput.value;
            }

            var formData = new FormData();
            formData.append('announcement_id', announcementId);
            if (csrfToken) {
                formData.append('_csrf_token', csrfToken);
            }

            var ackUrl = form ? form.getAttribute('action') : '/admin/announcements/acknowledge';

            fetch(ackUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            }).catch(function () {
                // Background acknowledgment failed or offline; server state persists on next action
            });
        }

        if (form) {
            form.addEventListener('submit', handleAcknowledge);
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', handleAcknowledge);
        }

        // Handle Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-visible')) {
                handleAcknowledge(e);
            }

            // Trap focus within dialog
            if (e.key === 'Tab' && modal.classList.contains('is-visible') && dialog) {
                var focusable = dialog.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                if (focusable.length === 0) {
                    return;
                }

                var first = focusable[0];
                var last = focusable[focusable.length - 1];

                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        last.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === last) {
                        first.focus();
                        e.preventDefault();
                    }
                }
            }
        });

        // If modal was rendered as visible from server (login / page load)
        if (modal.classList.contains('is-visible')) {
            var currentId = modal.getAttribute('data-announcement-id');
            if (currentId) {
                previouslyFocusedElement = document.activeElement;
                if (ackBtn) {
                    ackBtn.focus();
                }
            }
        }

        // Real-time polling for active announcement broadcasts
        function pollActiveAnnouncement() {
            if (isPolling) {
                return;
            }
            if (modal.classList.contains('is-visible')) {
                return;
            }

            isPolling = true;

            fetch('/admin/announcements/active', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                isPolling = false;
                if (data && data.active && data.announcement) {
                    var a = data.announcement;
                    if (!acknowledgedIds[a.id]) {
                        showModal(a.id, a.title, a.body);
                    }
                }
            })
            .catch(function () {
                isPolling = false;
            });
        }

        // Poll every 25 seconds when page is active
        var pollInterval = setInterval(pollActiveAnnouncement, 25000);

        // Also check when tab gains visibility
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                pollActiveAnnouncement();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnnouncementModal);
    } else {
        initAnnouncementModal();
    }
})();
