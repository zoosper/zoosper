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

        Array.prototype.slice.call(root.childNodes).forEach(function (node) {
            if (node.nodeType === Node.TEXT_NODE) {
                var plainText = node.textContent.trim();
                if (plainText !== '') {
                    blocks.push({ type: 'paragraph', data: { text: textToSafeInlineHtml(plainText) } });
                }
                return;
            }

            if (node.nodeType !== Node.ELEMENT_NODE) {
                return;
            }

            var element = node;
            var tag = element.tagName.toLowerCase();
            if (tag === 'script' || tag === 'style' || tag === 'iframe' || tag === 'object' || tag === 'embed') {
                return;
            }

            var htmlText = element.innerHTML.trim();
            if (htmlText !== '') {
                blocks.push({ type: 'paragraph', data: { text: htmlText } });
            }
        });

        if (blocks.length === 0) {
            blocks.push({ type: 'paragraph', data: { text: '' } });
        }

        return { time: Date.now(), blocks: blocks, version: '2.x' };
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
            timer = setTimeout(function () { callback.apply(null, args); }, delay);
        };
    }

    function setStatus(status, text) {
        if (status) {
            status.textContent = text;
        }
    }

    function activateEditor(wrapper, holder, textarea, status) {
        holder.setAttribute('aria-hidden', 'false');
        wrapper.classList.add('is-editorjs-ready');
        wrapper.classList.add('is-editorjs-active');
        textarea.setAttribute('data-zoosper-editor-source', 'html');
        setStatus(status, 'Editor.js ready. Saves still pass through server-side HTML sanitisation.');
    }

    function fallbackToTextarea(wrapper, holder, textarea, status, message) {
        wrapper.classList.remove('is-editorjs-ready');
        wrapper.classList.remove('is-editorjs-active');
        holder.setAttribute('aria-hidden', 'true');
        textarea.removeAttribute('data-zoosper-editor-source');
        setStatus(status, message || 'Textarea fallback active.');
    }

    function initialiseEditor(wrapper) {
        var status = wrapper.querySelector('.zoosper-content-editor__status');
        var holder = wrapper.querySelector('.zoosper-content-editor__holder');
        var textarea = wrapper.querySelector('textarea[name="content"]');
        var form = textarea ? textarea.closest('form') : null;

        if (!holder || !textarea) {
            return;
        }

        fallbackToTextarea(wrapper, holder, textarea, status, 'Textarea fallback active while Editor.js loads.');

        if (typeof window.EditorJS !== 'function') {
            fallbackToTextarea(wrapper, holder, textarea, status, 'Textarea fallback active. Local Editor.js bundle was not loaded.');
            return;
        }

        var editor = new window.EditorJS({
            holder: holder.id,
            data: htmlToEditorData(textarea.value),
            placeholder: 'Start writing page content...',
            onChange: debounce(function () {
                editor.save().then(function (data) {
                    textarea.value = editorDataToHtml(data);
                }).catch(function () {
                    fallbackToTextarea(wrapper, holder, textarea, status, 'Editor sync failed. Textarea fallback remains available.');
                });
            }, 250)
        });

        editor.isReady.then(function () {
            activateEditor(wrapper, holder, textarea, status);
            return editor.save();
        }).then(function (data) {
            textarea.value = editorDataToHtml(data);
        }).catch(function () {
            fallbackToTextarea(wrapper, holder, textarea, status, 'Editor.js failed to initialise. Textarea fallback remains available.');
        });

        if (form) {
            form.addEventListener('submit', function (event) {
                if (!wrapper.classList.contains('is-editorjs-ready')) {
                    return;
                }

                event.preventDefault();
                editor.save().then(function (data) {
                    textarea.value = editorDataToHtml(data);
                    form.submit();
                }).catch(function () {
                    fallbackToTextarea(wrapper, holder, textarea, status, 'Editor submit sync failed. Submitting textarea fallback.');
                    form.submit();
                });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-zoosper-editor="editorjs"]').forEach(initialiseEditor);
    });
}());
