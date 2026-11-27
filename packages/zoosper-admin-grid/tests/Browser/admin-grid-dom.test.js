import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const root = new URL('../../../../', import.meta.url);
const script = async (name) => readFile(new URL(`packages/zoosper-admin-grid/resources/admin/js/${name}`, root), 'utf8');
const tick = () => new Promise((resolve) => setTimeout(resolve, 0));

const boot = async (html, names) => {
    const dom = new JSDOM(html, {runScripts: 'outside-only', url: 'https://example.test/admin/pages'});
    dom.window.requestAnimationFrame = (callback) => callback();
    dom.window.matchMedia = () => ({matches: false, addEventListener() {}, removeEventListener() {}});
    dom.window.HTMLElement.prototype.focus = function focus() { this.dataset.focused = 'true'; };
    for (const name of names) dom.window.eval(await script(name));
    dom.window.document.dispatchEvent(new dom.window.Event('DOMContentLoaded', {bubbles: true}));
    await tick();
    return dom;
};

const compactFixture = () => `
<div class="admin-content">
  <section data-grid-workspace>
    <button type="button" data-grid-toggle="filters" aria-expanded="false">Filters</button>
    <button type="button" data-grid-toggle="columns" aria-expanded="false">Columns</button>
    <button type="button" data-grid-settings-toggle aria-expanded="false">Views</button>
    <span class="grid-compact-status">Saved</span>
    <form data-grid-filter-form><input name="page" value="7"><select data-grid-page-size><option value="50" selected>50</option></select>
      <div data-grid-panel="filters" hidden><button type="button" data-grid-panel-close>Close</button></div>
      <div data-grid-panel="columns" hidden><button type="button" data-grid-panel-close>Close</button>
        <div data-grid-column-list>
          <label class="grid-compact-column" data-column-key="id"><input name="visible_columns[]" value="id" checked><button type="button" data-grid-column-move="down">Down</button></label>
          <label class="grid-compact-column" data-column-key="title"><input name="visible_columns[]" value="title" checked><button type="button" data-grid-column-move="down">Down</button></label>
          <label class="grid-compact-column" data-column-key="status"><input name="visible_columns[]" value="status" checked><button type="button" data-grid-column-move="up">Up</button></label>
          <label class="grid-compact-column" data-column-key="actions"><input name="visible_columns[]" value="actions" checked><button type="button" data-grid-column-move="up">Up</button></label>
        </div>
      </div>
    </form>
  </section>
  <details data-grid-settings hidden><input name="view_name"><form data-grid-column-state-form></form></details>
  <table class="grid-table"><thead><tr><th data-grid-column="id">ID</th><th data-grid-column="title">Title</th><th data-grid-column="status">Status</th><th data-grid-column="actions">Actions</th></tr></thead><tbody><tr><td data-grid-column="id">1</td><td data-grid-column="title">Home</td><td data-grid-column="status">Draft</td><td data-grid-column="actions">Edit</td></tr></tbody></table>
</div>`;

test('compact disclosures are exclusive and keep ARIA state aligned', async () => {
    const dom = await boot(compactFixture(), ['grid-compact-workspace.js']);
    const {document} = dom.window;
    const filters = document.querySelector('[data-grid-toggle="filters"]');
    const columns = document.querySelector('[data-grid-toggle="columns"]');
    filters.click();
    assert.equal(document.querySelector('[data-grid-panel="filters"]').hidden, false);
    assert.equal(filters.getAttribute('aria-expanded'), 'true');
    columns.click();
    assert.equal(document.querySelector('[data-grid-panel="filters"]').hidden, true);
    assert.equal(filters.getAttribute('aria-expanded'), 'false');
    assert.equal(document.querySelector('[data-grid-panel="columns"]').hidden, false);
    assert.equal(columns.getAttribute('aria-expanded'), 'true');
});

test('column movement reflects table order, hidden form state and dirty status', async () => {
    const dom = await boot(compactFixture(), ['grid-compact-column-order.js']);
    const {document} = dom.window;
    document.querySelector('[data-column-key="status"] [data-grid-column-move="up"]').click();
    const listOrder = [...document.querySelectorAll('[data-grid-column-list] [data-column-key]')].map((node) => node.dataset.columnKey);
    const tableOrder = [...document.querySelectorAll('tbody td')].map((node) => node.dataset.gridColumn);
    const persisted = [...document.querySelectorAll('[data-grid-column-state-form] input[name="column_order[]"]')].map((node) => node.value);
    assert.deepEqual(listOrder, ['id', 'status', 'title', 'actions']);
    assert.deepEqual(tableOrder, listOrder);
    assert.deepEqual(persisted, listOrder);
    assert.equal(document.querySelector('.grid-compact-status').textContent, 'Unsaved changes');
    assert.equal(document.querySelector('[data-column-key="id"]').draggable, false);
    assert.equal(document.querySelector('[data-column-key="actions"]').draggable, false);
});

test('locked boundary columns cannot move through keyboard controls', async () => {
    const dom = await boot(compactFixture(), ['grid-compact-column-order.js']);
    const {document} = dom.window;
    document.querySelector('[data-column-key="id"] [data-grid-column-move="down"]').click();
    document.querySelector('[data-column-key="actions"] [data-grid-column-move="up"]').click();
    assert.deepEqual(
        [...document.querySelectorAll('[data-grid-column-list] [data-column-key]')].map((node) => node.dataset.columnKey),
        ['id', 'title', 'status', 'actions'],
    );
});

test('page size resets pagination and submits the canonical filter form', async () => {
    const dom = await boot(compactFixture(), ['grid-compact-workspace.js']);
    const {document, Event} = dom.window;
    const form = document.querySelector('[data-grid-filter-form]');
    let submitted = 0;
    form.requestSubmit = () => { submitted += 1; };
    const select = document.querySelector('[data-grid-page-size]');
    select.value = '50';
    select.dispatchEvent(new Event('change', {bubbles: true}));
    assert.equal(form.querySelector('[name="page"]').getAttribute('value'), '1');
    assert.equal(form.querySelector('[name="page_size"]').value, '50');
    assert.equal(submitted, 1);
});

test('page jump reads pagination status without modifying matching row content', async () => {
    const dom = await boot(`<section data-grid-workspace><label>Per page <select data-grid-page-size><option>20</option></select></label></section>
        <table class="grid-table"><tbody><tr><td>Page 1 of 5</td></tr></tbody></table>
        <nav class="grid-pagination"><span class="grid-pagination__status">Page 2 of 12</span></nav>`, ['grid-page-jump.js']);
    const {document} = dom.window;
    assert.equal(document.querySelector('td').textContent, 'Page 1 of 5');
    const input = document.querySelector('.grid-pagination [data-grid-page-jump] input[name="page"]');
    assert.equal(input.value, '2');
    assert.equal(input.max, '12');
});

test('page jump leaves matching content alone when pagination is absent', async () => {
    const dom = await boot('<section data-grid-workspace></section><p>Page 1 of 5</p>', ['grid-page-jump.js']);
    assert.equal(dom.window.document.querySelector('p').textContent, 'Page 1 of 5');
    assert.equal(dom.window.document.querySelector('[data-grid-page-jump]'), null);
});

test('selected CSV neutralises spreadsheet formulas in headers and cells', async () => {
    const dom = await boot(`<table class="grid-has-selection"><thead><tr><th>Select</th><th>=Header</th><th>Title</th></tr></thead>
        <tbody><tr class="is-selected"><td></td><td>=1+1</td><td>normal, "quoted"</td></tr>
        <tr class="is-selected"><td></td><td> +SUM(A1)</td><td>@cmd</td></tr>
        <tr class="is-selected"><td></td><td>-1</td><td>plain</td></tr></tbody></table>
        <div data-grid-selection-bar><select aria-label="Bulk actions"><option value="export.selected">Export</option></select></div>`, []);
    let csv;
    dom.window.Blob = class { constructor(parts) { csv = parts.join(''); } };
    dom.window.URL.createObjectURL = () => 'blob:test';
    dom.window.URL.revokeObjectURL = () => {};
    dom.window.HTMLAnchorElement.prototype.click = () => {};
    dom.window.eval(await script('grid-export-selected.js'));
    dom.window.document.querySelector('select').dispatchEvent(new dom.window.Event('change'));
    assert.equal(csv, '\uFEFF"\'=Header","Title"\r\n"\'=1+1","normal, ""quoted"""\r\n"\'+SUM(A1)","\'@cmd"\r\n"\'-1","plain"');
});
