import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const source = await readFile(new URL('../../resources/assets/js/dashboard-personalisation.js', import.meta.url), 'utf8');
const item = (code, title, checked = true) => `
<li data-dashboard-order-item="${code}">
  <input data-dashboard-order-input name="widget_order[]" value="stale-${code}">
  <label class="dashboard-personalisation__visibility"><input type="checkbox" data-dashboard-visibility ${checked ? 'checked' : ''}><span>${title}</span></label>
  <button type="button" data-dashboard-move="up">Up</button>
  <button type="button" data-dashboard-move="down">Down</button>
  <button type="button" draggable="true" data-dashboard-drag-handle>Drag</button>
</li>`;
const fixture = ({withGrid = true} = {}) => `
<details data-dashboard-personalisation>
  <form data-dashboard-personalisation-form>
    <ol data-dashboard-widget-order>${item('one', 'One')}${item('two', 'Two')}${item('three', 'Three', false)}</ol>
    <p data-dashboard-order-status></p>
  </form>
</details>
${withGrid ? '<span data-dashboard-visible-count>0</span><div data-dashboard-widget-grid><article data-dashboard-widget="one"><button data-dashboard-card-drag draggable="true">Drag</button></article><article data-dashboard-widget="two"><button data-dashboard-card-drag draggable="true">Drag</button></article><article data-dashboard-widget="three" hidden><button data-dashboard-card-drag draggable="true">Drag</button></article></div><div data-dashboard-hidden-empty></div>' : ''}`;
const boot = (options = {}) => {
    const dom = new JSDOM(fixture(options), {runScripts: 'outside-only', url: 'https://example.test/admin'});
    dom.window.eval(source);
    return dom;
};
const order = (document) => Array.from(document.querySelectorAll('[data-dashboard-order-item]'), (node) => node.dataset.dashboardOrderItem);

test('synchronises initial order visibility cards count and empty state', () => {
    const dom = boot();
    const {document} = dom.window;
    assert.deepEqual(Array.from(document.querySelectorAll('[data-dashboard-order-input]'), (input) => input.value), ['one', 'two', 'three']);
    assert.equal(document.querySelector('[data-dashboard-widget="three"]').hidden, true);
    assert.equal(document.querySelector('[data-dashboard-visible-count]').textContent, '2');
    assert.equal(document.querySelector('[data-dashboard-hidden-empty]').hidden, true);
});

test('moves widgets by controls and keeps submitted order and cards aligned', () => {
    const dom = boot();
    const {document} = dom.window;
    const two = document.querySelector('[data-dashboard-order-item="two"]');
    const up = two.querySelector('[data-dashboard-move="up"]');
    up.click();
    assert.deepEqual(order(document), ['two', 'one', 'three']);
    assert.deepEqual(Array.from(document.querySelectorAll('[data-dashboard-order-input]'), (input) => input.value), ['two', 'one', 'three']);
    assert.deepEqual(Array.from(document.querySelectorAll('[data-dashboard-widget]'), (card) => card.dataset.dashboardWidget), ['two', 'one', 'three']);
    assert.equal(document.querySelector('[data-dashboard-order-status]').textContent, 'Two moved up. Save the layout to keep this order.');
    assert.equal(document.activeElement, up);
});

test('updates visibility feedback and the all-hidden state', () => {
    const dom = boot();
    const {document, Event} = dom.window;
    for (const checkbox of document.querySelectorAll('[data-dashboard-visibility]')) {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event('change', {bubbles: true}));
    }
    assert.equal(document.querySelector('[data-dashboard-visible-count]').textContent, '0');
    assert.equal(document.querySelector('[data-dashboard-hidden-empty]').hidden, false);
    assert.equal(document.querySelector('[data-dashboard-order-status]').textContent, 'Hidden on this page. Save the layout to keep the change.');
    assert.equal(Array.from(document.querySelectorAll('[data-dashboard-widget]')).every((card) => card.hidden), true);
});

test('reorders through drag and drop while opening personalisation from the card grid', () => {
    const dom = boot();
    const {document, Event} = dom.window;
    const details = document.querySelector('[data-dashboard-personalisation]');
    const draggedHandle = document.querySelector('[data-dashboard-widget="three"] [data-dashboard-card-drag]');
    const targetCard = document.querySelector('[data-dashboard-widget="one"]');
    draggedHandle.dispatchEvent(new Event('dragstart', {bubbles: true, cancelable: true}));
    targetCard.dispatchEvent(new Event('drop', {bubbles: true, cancelable: true}));
    assert.deepEqual(order(document), ['three', 'one', 'two']);
    assert.equal(details.open, true);
    assert.equal(document.querySelector('[data-dashboard-order-status]').textContent, 'Widget order changed. Save the layout to keep this order.');
});

test('supports the role-default form when no live Dashboard grid exists', () => {
    const dom = boot({withGrid: false});
    const {document} = dom.window;
    document.querySelector('[data-dashboard-order-item="two"] [data-dashboard-move="up"]').click();
    assert.deepEqual(order(document), ['two', 'one', 'three']);
    assert.deepEqual(Array.from(document.querySelectorAll('[data-dashboard-order-input]'), (input) => input.value), ['two', 'one', 'three']);
});
