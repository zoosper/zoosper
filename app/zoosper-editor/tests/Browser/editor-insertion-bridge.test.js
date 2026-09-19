import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const source = await readFile(new URL('../../../../public/assets/admin/js/zoosper-editor-bridge.js', import.meta.url), 'utf8');
const fixture = () => {
    const dom = new JSDOM('<div data-zoosper-editor="editorjs"><input data-zoosper-editor-json></div><div data-zoosper-editor="editorjs"><input data-zoosper-editor-json></div>', {runScripts: 'outside-only'});
    dom.window.structuredClone = globalThis.structuredClone;
    dom.window.eval(source);
    return dom;
};
const makeEditor = () => {
    const calls = [];
    return {calls, instance: {isReady: Promise.resolve(), blocks: {getCurrentBlockIndex: () => 1, insert: (...args) => calls.push(args)}, save: async () => ({time: 1, blocks: [{type: 'image'}]})}};
};

test('keeps instances scoped to their wrappers', async () => {
    const dom = fixture();
    const wrappers = dom.window.document.querySelectorAll('[data-zoosper-editor]');
    const first = makeEditor();
    const second = makeEditor();
    dom.window.ZoosperEditorBridge.register(wrappers[0], first.instance);
    dom.window.ZoosperEditorBridge.register(wrappers[1], second.instance);
    await dom.window.ZoosperEditorBridge.insert(wrappers[1], 'image', {file: {url: '/media/two.jpg'}});
    assert.equal(first.calls.length, 0);
    assert.equal(second.calls.length, 1);
});

test('awaits readiness inserts after the current block and synchronises JSON', async () => {
    const dom = fixture();
    const wrapper = dom.window.document.querySelector('[data-zoosper-editor]');
    let ready;
    const calls = [];
    const instance = {isReady: new Promise((resolve) => { ready = resolve; }), blocks: {getCurrentBlockIndex: () => 2, insert: (...args) => calls.push(args)}, save: async () => ({time: 2, blocks: [{type: 'image'}]})};
    dom.window.ZoosperEditorBridge.register(wrapper, instance);
    const pending = dom.window.ZoosperEditorBridge.insert(wrapper, 'image', {file: {url: '/media/a.jpg'}});
    await Promise.resolve();
    assert.equal(calls.length, 0);
    ready();
    await pending;
    assert.deepEqual(calls[0], ['image', {file: {url: '/media/a.jpg'}}, undefined, 3, true, false]);
    assert.match(wrapper.querySelector('[data-zoosper-editor-json]').value, /"type":"image"/);
});

test('fails closed for missing instances unsupported blocks and unmanaged URLs', async () => {
    const dom = fixture();
    const wrapper = dom.window.document.querySelector('[data-zoosper-editor]');
    await assert.rejects(() => dom.window.ZoosperEditorBridge.insert(wrapper, 'image', {file: {url: '/media/a.jpg'}}), /not ready/);
    dom.window.ZoosperEditorBridge.register(wrapper, makeEditor().instance);
    await assert.rejects(() => dom.window.ZoosperEditorBridge.insert(wrapper, 'paragraph', {text: 'No'}), /Unsupported/);
    await assert.rejects(() => dom.window.ZoosperEditorBridge.insert(wrapper, 'image', {file: {url: 'https://example.test/a.jpg'}}), /managed \/media\//);
});
