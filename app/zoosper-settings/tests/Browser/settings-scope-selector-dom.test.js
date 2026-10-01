import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const source = await readFile(new URL('../../resources/assets/js/settings-workspace.js', import.meta.url), 'utf8');
const options = JSON.stringify({
    default: {},
    website: {'base': 'Base website', 'outlet': 'Outlet website'},
    store: {'main': 'Main store'},
    site: {'1': 'Primary site'},
});
const fixture = ({scope = 'default', selected = ''} = {}) => `
<select id="settings-scope-type"><option value="default" ${scope === 'default' ? 'selected' : ''}>Default</option><option value="website" ${scope === 'website' ? 'selected' : ''}>Website</option><option value="store">Store</option><option value="site">Site</option></select>
<label id="settings-scope-key-label" class="settings-hidden"><select id="settings-scope-key" data-selected="${selected}"></select></label>
<script type="application/json" id="settings-scope-options">${options}</script>`;
const boot = (configuration = {}) => {
    const dom = new JSDOM(fixture(configuration), {runScripts: 'outside-only', url: 'https://example.test/admin/settings'});
    dom.window.eval(source);
    return dom;
};

test('exits safely when Settings-only DOM is absent', () => {
    const dom = new JSDOM('<main>Unrelated Admin screen</main>', {runScripts: 'outside-only'});
    assert.doesNotThrow(() => dom.window.eval(source));
});

test('keeps the Default scope key hidden disabled and empty', () => {
    const {window} = boot();
    const label = window.document.getElementById('settings-scope-key-label');
    const key = window.document.getElementById('settings-scope-key');
    assert.equal(label.classList.contains('settings-hidden'), true);
    assert.equal(key.disabled, true);
    assert.equal(key.options.length, 0);
});

test('builds the selected scoped options from the inert JSON catalogue', () => {
    const {window} = boot({scope: 'website', selected: 'outlet'});
    const label = window.document.getElementById('settings-scope-key-label');
    const key = window.document.getElementById('settings-scope-key');
    assert.equal(label.classList.contains('settings-hidden'), false);
    assert.equal(key.disabled, false);
    assert.deepEqual(Array.from(key.options, (option) => [option.value, option.text]), [['base', 'Base website'], ['outlet', 'Outlet website']]);
    assert.equal(key.value, 'outlet');
});

test('clears the prior selection and rebuilds when scope changes', () => {
    const {window} = boot({scope: 'website', selected: 'outlet'});
    const type = window.document.getElementById('settings-scope-type');
    const key = window.document.getElementById('settings-scope-key');
    type.value = 'store';
    type.dispatchEvent(new window.Event('change', {bubbles: true}));
    assert.equal(key.dataset.selected, '');
    assert.deepEqual(Array.from(key.options, (option) => [option.value, option.text]), [['main', 'Main store']]);
    assert.equal(key.disabled, false);
});
