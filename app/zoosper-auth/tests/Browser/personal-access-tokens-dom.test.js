import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const source = await readFile(new URL('../../resources/assets/admin/js/personal-access-tokens.js', import.meta.url), 'utf8');

const fixture = () => `
<div data-pat-screen>
  <a href="#pat-create" data-pat-focus-create>New token</a>
  <code id="pat-one-time-secret">zp_pat_example_secret</code>
  <button type="button" data-pat-copy data-copy-target="pat-one-time-secret">Copy token</button>
  <p data-pat-copy-status></p>
  <form data-pat-form id="pat-create">
    <input data-pat-name name="name">
    <output data-pat-scope-count></output>
    <p data-pat-selection-help></p>
    <button type="button" data-pat-select-all>Select all</button>
    <button type="button" data-pat-clear>Clear</button>
    <fieldset data-pat-scope-group>
      <button type="button" data-pat-select-group>Select group</button>
      <label class="pat-scope-chip pat-scope-chip--read"><input type="checkbox" name="scopes[]" value="pages:read"></label>
      <label class="pat-scope-chip pat-scope-chip--destructive"><input type="checkbox" name="scopes[]" value="pages:delete"></label>
    </fieldset>
    <fieldset data-pat-scope-group>
      <button type="button" data-pat-select-group>Select group</button>
      <label class="pat-scope-chip pat-scope-chip--write"><input type="checkbox" name="scopes[]" value="media:upload"></label>
    </fieldset>
    <button type="submit" data-pat-create>Create token</button>
  </form>
  <section class="pat-token-list">
    <div class="pat-grid-scroll">
      <div data-grid-workspace>
        <div class="grid-compact-actions">
          <form data-grid-filter-form>
            <label>Query<input name="q"></label>
            <input name="page" value="4">
          </form>
          <a data-grid-export href="/admin/access-tokens/export">Export</a>
        </div>
      </div>
      <p>Showing 1-10 of 20</p>
      <table class="grid-table"></table>
      <nav class="grid-pagination">
        <a class="grid-pagination__prev" rel="prev">Previous</a>
        <span class="grid-pagination__status">Page 1 of 2</span>
        <a class="grid-pagination__next" rel="next">Next</a>
      </nav>
    </div>
  </section>
</div>`;

const boot = ({clipboard = true} = {}) => {
    const dom = new JSDOM(fixture(), {runScripts: 'outside-only', url: 'https://example.test/admin/access-tokens'});
    if (clipboard) {
        Object.defineProperty(dom.window.navigator, 'clipboard', {value: {writeText: async () => undefined}, configurable: true});
    }
    dom.window.HTMLFormElement.prototype.requestSubmit = function requestSubmit() { this.dataset.submitted = 'true'; };
    dom.window.eval(source);
    return dom;
};

test('updates selection count, destructive help and group controls', () => {
    const dom = boot();
    const {document, Event} = dom.window;
    const form = document.querySelector('[data-pat-form]');
    const create = form.querySelector('[data-pat-create]');
    const count = form.querySelector('[data-pat-scope-count]');
    const help = form.querySelector('[data-pat-selection-help]');
    const firstGroup = form.querySelector('[data-pat-scope-group]');
    assert.equal(create.disabled, true);
    assert.equal(count.textContent, '0 of 3 selected');
    firstGroup.querySelector('[data-pat-select-group]').click();
    assert.equal(count.textContent, '2 of 3 selected');
    assert.match(help.textContent, /2 scopes selected · 1 destructive/);
    assert.equal(firstGroup.dataset.hasSelection, 'true');
    assert.equal(firstGroup.querySelector('[data-pat-select-group]').textContent, 'Clear group');
    form.querySelector('[data-pat-select-all]').click();
    assert.equal(count.textContent, '3 of 3 selected');
    form.querySelector('[data-pat-clear]').click();
    assert.equal(create.disabled, true);
    assert.equal(help.textContent, 'Pick at least one scope to continue.');
    const first = form.querySelector('input[name="scopes[]"]');
    first.checked = true;
    first.dispatchEvent(new Event('change', {bubbles: true}));
    assert.equal(create.disabled, false);
});

test('copies the one-time token and reports success', async () => {
    const dom = boot();
    const {document} = dom.window;
    document.querySelector('[data-pat-copy]').click();
    await new Promise((resolve) => dom.window.setTimeout(resolve, 0));
    assert.equal(document.querySelector('[data-pat-copy]').textContent, 'Copied');
    assert.equal(document.querySelector('[data-pat-copy-status]').textContent, 'Token copied to the clipboard.');
});

test('enhances the owner-scoped Grid without exposing unsupported export', () => {
    const dom = boot();
    const {document, KeyboardEvent} = dom.window;
    const screen = document.querySelector('[data-pat-screen]');
    const form = document.querySelector('[data-grid-filter-form]');
    const query = document.querySelector('.pat-grid-search input[name="q"]');
    const pagination = document.querySelector('.grid-pagination');
    const exportControl = document.querySelector('[data-grid-export]');
    assert.equal(screen.dataset.patGridEnhanced, 'true');
    assert.equal(query.placeholder, 'Search tokens');
    assert.equal(query.getAttribute('aria-label'), 'Search tokens');
    assert.equal(exportControl.hidden, true);
    assert.equal(exportControl.getAttribute('aria-hidden'), 'true');
    assert.equal(pagination.hasAttribute('data-pat-pagination'), true);
    assert.equal(pagination.getAttribute('aria-label'), 'Access Tokens pagination');
    query.dispatchEvent(new KeyboardEvent('keydown', {key: 'Enter', bubbles: true}));
    assert.equal(form.querySelector('[name="page"]').value, '1');
    assert.equal(form.dataset.submitted, 'true');
});
