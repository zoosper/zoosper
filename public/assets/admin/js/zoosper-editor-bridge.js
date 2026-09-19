(() => {
    'use strict';

    const instances = new WeakMap();
    const allowedTypes = new Set(['image']);

    const assertWrapper = (wrapper) => {
        if (!(wrapper instanceof Element) || wrapper.dataset.zoosperEditor !== 'editorjs') {
            throw new TypeError('A Zoosper Editor.js wrapper is required.');
        }
    };

    const synchronise = async (wrapper, editor) => {
        const field = wrapper.querySelector('[data-zoosper-editor-json]');
        if (!(field instanceof HTMLInputElement)) {
            throw new Error('Editor structured-state field is unavailable.');
        }
        const output = await editor.save();
        field.value = JSON.stringify(output);
        field.dispatchEvent(new Event('change', {bubbles: true}));
        return output;
    };

    const normalisePayload = (type, data) => {
        if (!allowedTypes.has(type)) {
            throw new TypeError(`Unsupported editor block type: ${type}`);
        }
        if (data === null || typeof data !== 'object' || Array.isArray(data)) {
            throw new TypeError('Editor block data must be an object.');
        }
        if (type === 'image') {
            const url = data.file?.url;
            if (typeof url !== 'string' || !url.startsWith('/media/')) {
                throw new TypeError('Image blocks require a managed /media/ URL.');
            }
        }
        return structuredClone(data);
    };

    const register = (wrapper, editor) => {
        assertWrapper(wrapper);
        if (editor === null || typeof editor !== 'object' || typeof editor.save !== 'function') {
            throw new TypeError('A valid Editor.js instance is required.');
        }
        instances.set(wrapper, editor);
        wrapper.dispatchEvent(new CustomEvent('zoosper:editor-ready'));
    };

    const insert = async (wrapper, type, data) => {
        assertWrapper(wrapper);
        const editor = instances.get(wrapper);
        if (!editor) {
            throw new Error('Editor.js instance is not ready for this wrapper.');
        }
        await editor.isReady;
        if (typeof editor.blocks?.insert !== 'function') {
            throw new Error('Editor.js block insertion API is unavailable.');
        }
        const payload = normalisePayload(type, data);
        const current = typeof editor.blocks.getCurrentBlockIndex === 'function'
            ? editor.blocks.getCurrentBlockIndex()
            : -1;
        const index = Number.isInteger(current) && current >= 0 ? current + 1 : undefined;
        editor.blocks.insert(type, payload, undefined, index, true, false);
        await synchronise(wrapper, editor);
        wrapper.dispatchEvent(new CustomEvent('zoosper:editor-block-inserted', {
            detail: {type},
        }));
    };

    window.ZoosperEditorBridge = Object.freeze({register, insert});
})();
