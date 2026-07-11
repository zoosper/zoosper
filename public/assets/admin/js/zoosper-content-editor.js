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

    function allowedHeadingLevel(value) {
        var level = parseInt(value, 10);
        return [2, 3, 4].indexOf(level) !== -1 ? level : 2;
    }

    function listItemsFromElement(listElement) {
        return Array.prototype.slice.call(listElement.children).filter(function (child) {
            return child.tagName && child.tagName.toLowerCase() === 'li';
        }).map(function (item) {
            return {
                content: item.innerHTML.trim(),
                meta: {},
                items: []
            };
        }).filter(function (item) {
            return item.content !== '';
        });
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

            if (tag === 'h2' || tag === 'h3' || tag === 'h4') {
                blocks.push({
                    type: 'header',
                    data: {
                        text: element.innerHTML.trim(),
                        level: allowedHeadingLevel(tag.replace('h', ''))
                    }
                });
                return;
            }

            if (tag === 'ul' || tag === 'ol') {
                var items = listItemsFromElement(element);
                if (items.length > 0) {
                    blocks.push({
                        type: 'list',
                        data: {
                            style: tag === 'ol' ? 'ordered' : 'unordered',
                            items: items
                        }
                    });
                }
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

    function renderListItems(items) {
        if (!Array.isArray(items)) {
            return '';
        }

        return items.map(function (item) {
            var content = item && typeof item.content === 'string' ? item.content : '';
            var nested = item && Array.isArray(item.items) && item.items.length > 0
                ? '<ul>' + renderListItems(item.items) + '</ul>'
                : '';

            return content.trim() === '' && nested === '' ? '' : '<li>' + content + nested + '</li>';
        }).filter(Boolean).join('');
    }

    function editorDataToHtml(data) {
        if (!data || !Array.isArray(data.blocks)) {
            return '';
        }

        return data.blocks.map(function (block) {
            if (!block || !block.type) {
                return '';
            }

            if (block.type === 'header') {
                var headerText = block.data && typeof block.data.text === 'string' ? block.data.text : '';
                var level = allowedHeadingLevel(block.data && block.data.level);
                return headerText.trim() === '' ? '' : '<h' + level + '>' + headerText + '</h' + level + '>';
            }

            if (block.type === 'list') {
                var style = block.data && block.data.style === 'ordered' ? 'ordered' : 'unordered';
                var tag = style === 'ordered' ? 'ol' : 'ul';
                var itemsHtml = renderListItems(block.data && block.data.items);
                return itemsHtml === '' ? '' : '<' + tag + '>' + itemsHtml + '</' + tag + '>';
            }

            if (block.type === 'paragraph') {
                var paragraphText = block.data && typeof block.data.text === 'string' ? block.data.text : '';
                return paragraphText.trim() === '' ? '' : '<p>' + paragraphText + '</p>';
            }

            return '';
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

    function buildToolsConfig() {
        var bundle = window.ZoosperEditorJsBundle || {};
        var tools = {};

        if (typeof bundle.Header === 'function') {
            tools.header = {
                class: bundle.Header,
                inlineToolbar: true,
                shortcut: 'CMD+SHIFT+H',
                config: {
                    placeholder: 'Heading',
                    levels: [2, 3, 4],
                    defaultLevel: 2
                }
            };
        }

        if (typeof bundle.EditorjsList === 'function') {
            tools.list = {
                class: bundle.EditorjsList,
                inlineToolbar: true,
                config: {
                    defaultStyle: 'unordered',
                    maxLevel: 3
                }
            };
        }

        return tools;
    }

    function activateEditor(wrapper, holder, textarea, status) {
        holder.setAttribute('aria-hidden', 'false');
        wrapper.classList.add('is-editorjs-ready');
        wrapper.classList.add('is-editorjs-active');
        textarea.setAttribute('data-zoosper-editor-source', 'html');
        setStatus(status, 'Editor.js ready with heading/list tools. Saves still pass through server-side HTML sanitisation.');
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
            tools: buildToolsConfig(),
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
