(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-zoosper-editor="editorjs"]').forEach(function (wrapper) {
            var status = wrapper.querySelector('.zoosper-content-editor__status');
            var holder = wrapper.querySelector('.zoosper-content-editor__holder');
            var textarea = wrapper.querySelector('textarea[name="content"]');

            if (!holder || !textarea) {
                return;
            }

            if (typeof window.EditorJS !== 'function') {
                if (status) {
                    status.textContent = 'Textarea fallback active. Run npm build to enable local Editor.js.';
                }
                return;
            }

            wrapper.classList.add('is-editorjs-active');
            if (status) {
                status.textContent = 'Local Editor.js bundle detected. Textarea remains source of truth until block_json support.';
            }

            // Phase 0.69 verifies local Editor.js can be bundled and loaded safely.
            // The actual Editor.js instance and block_json persistence are intentionally deferred.
        });
    });
}());
