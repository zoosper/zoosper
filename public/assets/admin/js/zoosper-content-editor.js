(function () {
    'use strict';

    function textToSafeInlineHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function htmlToEditorData(html) {
        var root = document.createElement('div');
        root.innerHTML = html || '';
        var blocks = [];

        Array.prototype.slice.call(root.children).forEach(function (element) {
            var tag = element.tagName.toLowerCase();
            var text = element.innerHTML.trim();

            if (text === '') {
                return;
            }

            // Phase 0.72 intentionally uses paragraph blocks only. Header/list
            // tools and true block_json storage are deferred to later phases.
            if (tag === 'script' || tag === 'style' || tag === 'iframe') {
                return;
            }

            blocks.push({
                type: 'paragraph',
                data: { text: text }
            });
        });

        if (blocks.length === 0 && root.textContent.trim() !== '') {
            blocks.push({
                type: 'paragraph',
                data: { text: textToSafeInlineHtml(root.textContent.trim()) }
            });
        }

        return {
            time: Date.now(),
            blocks: blocks,
            version: '2.x'
        };
    }

    function editorDataToHtml(data) {
        if (!data || !Array.isArray(data.blocks)) {
            return '';
        }

        return data.blocks.map(function (block) {
            if (!block || block.type !== 'paragraph') {
                return '';
            }

            var text = block.data && typeof block.data.text === 'string' ? block.data.text : '';
            return text.trim() === '' ? '' : '<p>' + text + '</p>';
        }).filter(Boolean).join('\n');
    }

    function debounce(callback, delay) {
        var timer = null;
        return function () {
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(null, args);
            }, delay);
        };
    }

    function initialiseEditor(wrapper) {
        var status = wrapper.querySelector('.zoosper-content-editor__status');
        var holder = wrapper.querySelector('.zoosper-content-editor__holder');
        var textarea = wrapper.querySelector('textarea[name="content"]');
        var form = textarea ? textarea.closest('form') : null;

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
            status.textContent = 'Editor.js active. Saving still uses sanitised HTML.';
        }

        var editor = new window.EditorJS({
            holder: holder.id,
            data: htmlToEditorData(textarea.value),
            placeholder: 'Start writing page content...',
            onChange: debounce(function () {
                editor.save().then(function (data) {
                    textarea.value = editorDataToHtml(data);
                }).catch(function () {
                    if (status) {
                        status.textContent = 'Editor sync failed. Textarea fallback remains available.';
                    }
                    wrapper.classList.remove('is-editorjs-active');
                });
            }, 250)
        });

        if (form) {
            form.addEventListener('submit', function (event) {
                if (!wrapper.classList.contains('is-editorjs-active')) {
                    return;
                }

                event.preventDefault();
                editor.save().then(function (data) {
                    textarea.value = editorDataToHtml(data);
                    form.submit();
                }).catch(function () {
                    wrapper.classList.remove('is-editorjs-active');
                    form.submit();
                });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-zoosper-editor="editorjs"]').forEach(initialiseEditor);
    });
}());
