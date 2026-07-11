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
                    status.textContent = 'Textarea fallback active';
                }
                return;
            }

            wrapper.classList.add('is-editorjs-active');
            if (status) {
                status.textContent = 'Editor.js adapter active';
            }

            // Phase 0.68 deliberately keeps textarea as the submitted source of truth.
            // A future block_json phase should map Editor.js data to validated JSON.
        });
    });
}());
