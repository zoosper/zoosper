import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const source = await readFile(new URL('../../resources/assets/js/settings-workspace.js', import.meta.url), 'utf8');
const button = (id, text, disabled = false) => `<button type="button" id="${id}" ${disabled ? 'disabled' : ''}>${text}</button>`;
const fixture = () => `
<header><details class="settings-help"><summary id="settings-help-summary">Help</summary><div><button id="help-action">Help action</button></div></details></header>
<div id="settings-workspace" data-density="comfortable">
<nav><button data-category-tab="general" aria-selected="true"><span>General</span></button><button data-category-tab="security" aria-selected="false"><span>Security</span></button></nav>
<main>
<input id="settings-search"><select id="settings-source-filter"><option value="all">All</option><option value="editable">Editable</option><option value="readonly">Read-only</option></select>
<select id="settings-module-filter"><option value="all">All modules</option><option value="core">core</option><option value="mail">mail</option></select>
<select id="settings-density"><option value="comfortable">Comfortable</option><option value="compact">Compact</option></select>
${button('settings-reset-view','Reset')}
<details class="settings-more-actions"><summary id="settings-actions-summary">More actions</summary><div id="settings-actions-panel"><section aria-labelledby="settings-action-search-title"><strong id="settings-action-search-title">Search</strong>${button('settings-previous-match','Previous',true)}${button('settings-next-match','Next',true)}<span id="settings-match-position"></span></section><section>${button('settings-save-view','Save')}<select id="settings-saved-view"><option value="">Saved views</option></select><span id="settings-saved-view-state"></span>${button('settings-update-view','Update',true)}${button('settings-restore-view','Restore',true)}${button('settings-pin-view','Pin',true)}${button('settings-duplicate-view','Duplicate',true)}${button('settings-default-view','Default',true)}${button('settings-clear-default-view','Clear default',true)}${button('settings-rename-view','Rename',true)}${button('settings-delete-view','Delete',true)}<input type="file" id="settings-import-views-file">${button('settings-import-views','Import')}${button('settings-export-views','Export')}${button('settings-download-views','Download')}${button('settings-view-diagnostics','Diagnostics')}${button('settings-repair-views','Repair')}${button('settings-reset-personal-workspace','Reset personal')}${button('settings-clear-views','Clear views',true)}${button('settings-copy-view','Copy link')}${button('settings-clear-target','Clear target')}${button('settings-print','Print')}</section><section>${button('settings-expand-all','Expand')}${button('settings-collapse-all','Collapse')}</section></div></details>
<span id="settings-active-state"></span><span id="settings-result-summary"></span><span id="settings-copy-status"></span><span id="settings-link-state"></span><span id="settings-url-state"></span><span id="settings-print-generated"></span><span id="settings-print-url"></span><div id="settings-empty"></div>
<section data-category-panel="general"><article data-settings-card data-settings-module="core"><details data-settings-group data-group-key="general.main" open><summary>Main</summary><div data-setting-field="site.name" id="field-site" data-setting-source="database" data-setting-readonly="false" data-setting-editable="true">Site name</div><form data-settings-form><input name="title" value="Initial"><span data-form-status></span>${button('reset-section','Reset section',true).replace('id="reset-section"','data-reset-section')}${button('save-section','Save section',true).replace('id="save-section"','data-save-section')}</form></details></article></section>
<section data-category-panel="security" hidden><article data-settings-card data-settings-module="mail"><details data-settings-group data-group-key="security.mail"><summary>Mail</summary><div data-setting-field="mail.host" id="field-mail" data-setting-source="project" data-setting-readonly="true" data-setting-editable="false">Mail host setting</div><div data-setting-field="mail.sender" id="field-sender" data-setting-source="database" data-setting-readonly="false" data-setting-editable="true">Mail sender setting</div></details></article></section>
</main></div>`;

const boot = ({url = 'https://example.test/admin/settings', storage = {}, confirms = [true], prompts = []} = {}) => {
    const dom = new JSDOM(fixture(), {runScripts: 'outside-only', url});
    const {window} = dom;
    Object.entries(storage).forEach(([key, value]) => window.localStorage.setItem(key, value));
    let confirmIndex = 0;
    let promptIndex = 0;
    window.confirm = () => confirms[Math.min(confirmIndex++, confirms.length - 1)] ?? true;
    window.prompt = () => prompts[promptIndex++] ?? null;
    window.requestAnimationFrame = (callback) => { callback(); return 1; };
    window.HTMLElement.prototype.scrollIntoView = function scrollIntoView() { this.dataset.scrolled = 'true'; };
    Object.defineProperty(window.navigator, 'clipboard', {value: {writeText: async () => undefined}, configurable: true});
    window.print = () => { window.__printed = true; };
    window.URL.createObjectURL = () => 'blob:test';
    window.URL.revokeObjectURL = () => undefined;
    window.eval(source);
    return dom;
};
const fire = (window, target, type) => target.dispatchEvent(new window.Event(type, {bubbles: true, cancelable: true}));
const key = (window, target, value, options = {}) => target.dispatchEvent(new window.KeyboardEvent('keydown', {key: value, bubbles: true, cancelable: true, ...options}));

test('filters settings and supports cyclic boundary and shortcut navigation', () => {
    const {window} = boot();
    const {document} = window;
    const search = document.getElementById('settings-search');
    search.value = 'mail'; fire(window, search, 'input');
    assert.equal(document.getElementById('settings-match-position').textContent, '2 matches');
    key(window, search, 'Enter');
    assert.equal(document.getElementById('settings-match-position').textContent, '1 of 2');
    assert.equal(window.location.hash, '#field-mail');
    key(window, search, 'End');
    assert.equal(document.getElementById('settings-match-position').textContent, '2 of 2');
    assert.equal(window.location.hash, '#field-sender');
    key(window, search, 'Home');
    assert.equal(window.location.hash, '#field-mail');
    key(window, search, 'Enter', {shiftKey: true});
    assert.equal(window.location.hash, '#field-sender');
    document.body.focus(); key(window, document, '/');
    assert.equal(document.activeElement, search);
    key(window, search, 'Escape');
    assert.equal(search.value, '');
});

test('keeps disclosures exclusive and restores trigger focus after Escape', () => {
    const {window} = boot();
    const {document} = window;
    const help = document.querySelector('.settings-help');
    const actions = document.querySelector('.settings-more-actions');
    help.open = true; fire(window, help, 'toggle');
    assert.equal(document.activeElement.id, 'help-action');
    actions.open = true; fire(window, actions, 'toggle');
    assert.equal(help.open, false);
    assert.equal(document.getElementById('settings-actions-summary').getAttribute('aria-expanded'), 'true');
    key(window, document, 'Escape');
    assert.equal(actions.open, false);
    assert.equal(document.activeElement.id, 'settings-actions-summary');
    actions.open = true; fire(window, actions, 'toggle');
    fire(window, document.body, 'pointerdown');
    assert.equal(actions.open, false);
});

test('tracks dirty section forms resets them and protects navigation', () => {
    const {window} = boot({storage: {'zoosper.settings.savedViews': JSON.stringify({version: 1, views: {Mail: {q: 'mail', view: 'all', module: 'mail', density: 'compact'}}})}, confirms: [false]});
    const {document} = window;
    const form = document.querySelector('[data-settings-form]');
    const input = form.querySelector('input[name="title"]');
    const save = form.querySelector('[data-save-section]');
    const reset = form.querySelector('[data-reset-section]');
    input.value = 'Changed'; fire(window, input, 'input');
    assert.equal(form.dataset.dirty, 'true');
    assert.equal(save.disabled, false);
    assert.equal(reset.disabled, false);
    assert.equal(form.querySelector('[data-form-status]').textContent, 'Unsaved changes');
    const beforeUnload = new window.Event('beforeunload', {cancelable: true});
    window.dispatchEvent(beforeUnload);
    assert.equal(beforeUnload.defaultPrevented, true);
    const saved = document.getElementById('settings-saved-view'); saved.value = 'Mail'; fire(window, saved, 'change');
    assert.equal(document.getElementById('settings-search').value, '');
    assert.match(document.getElementById('settings-copy-status').textContent, /Unsaved configuration changes/);
    reset.click();
    assert.equal(form.dataset.dirty, 'false');
    assert.equal(input.value, 'Initial');
    fire(window, form, 'submit');
    assert.equal(form.dataset.submitting, 'true');
});

test('applies saved views reports divergence and supports keyboard default and delete actions', () => {
    const savedEnvelope = JSON.stringify({version: 1, views: {Mail: {q: 'mail', view: 'editable', module: 'mail', density: 'compact'}}});
    const {window} = boot({storage: {'zoosper.settings.savedViews': savedEnvelope}});
    const {document} = window;
    const saved = document.getElementById('settings-saved-view'); saved.value = 'Mail'; fire(window, saved, 'change');
    assert.equal(document.getElementById('settings-search').value, 'mail');
    assert.equal(document.getElementById('settings-source-filter').value, 'editable');
    assert.equal(document.getElementById('settings-workspace').dataset.density, 'compact');
    assert.equal(document.getElementById('settings-saved-view-state').textContent, 'Saved view active');
    const search = document.getElementById('settings-search'); search.value = 'sender'; fire(window, search, 'input');
    assert.equal(document.getElementById('settings-saved-view-state').textContent, 'Modified from saved view');
    key(window, saved, 'd', {altKey: true});
    assert.equal(window.localStorage.getItem('zoosper.settings.defaultSavedView'), 'Mail');
    key(window, saved, 'Delete');
    const remaining = JSON.parse(window.localStorage.getItem('zoosper.settings.savedViews'));
    assert.deepEqual(remaining.views, {});
});

test('saves value-free views and restores a default only without explicit URL state', () => {
    const {window} = boot({prompts: ['Current']});
    const {document} = window;
    const search = document.getElementById('settings-search'); search.value = 'mail'; fire(window, search, 'input');
    document.getElementById('settings-source-filter').value = 'editable'; fire(window, document.getElementById('settings-source-filter'), 'change');
    document.getElementById('settings-save-view').click();
    const stored = JSON.parse(window.localStorage.getItem('zoosper.settings.savedViews'));
    assert.deepEqual(stored.views.Current, {q: 'mail', view: 'editable', module: 'all', density: 'comfortable'});
    assert.equal(JSON.stringify(stored).includes('Initial'), false);

    const defaults = {'zoosper.settings.savedViews': JSON.stringify({version: 1, views: {Default: {q: 'sender', view: 'all', module: 'mail', density: 'compact'}}}), 'zoosper.settings.defaultSavedView': 'Default'};
    const restored = boot({storage: defaults});
    assert.equal(restored.window.document.getElementById('settings-search').value, 'sender');
    const explicit = boot({url: 'https://example.test/admin/settings?q=mail', storage: defaults});
    assert.equal(explicit.window.document.getElementById('settings-search').value, 'mail');
});

test('synchronises allowlisted URL state and reapplies browser history state', () => {
    const {window} = boot({url: 'https://example.test/admin/settings?q=mail&view=editable&module=mail&density=compact#field-mail'});
    const {document} = window;
    assert.equal(document.getElementById('settings-search').value, 'mail');
    assert.equal(document.getElementById('settings-workspace').dataset.density, 'compact');
    assert.equal(document.getElementById('settings-link-state').textContent, 'Applied workspace state from link');
    const sourceFilter = document.getElementById('settings-source-filter'); sourceFilter.value = 'all'; fire(window, sourceFilter, 'change');
    assert.equal(new window.URL(window.location.href).searchParams.has('view'), false);
    window.history.replaceState(null, '', '/admin/settings?q=site&module=core&density=comfortable#field-site');
    window.dispatchEvent(new window.PopStateEvent('popstate'));
    assert.equal(document.getElementById('settings-search').value, 'site');
    assert.equal(document.getElementById('settings-module-filter').value, 'core');
    document.getElementById('settings-reset-view').click();
    const resetUrl = new window.URL(window.location.href);
    assert.equal(resetUrl.search, '');
    assert.equal(resetUrl.hash, '#field-site');
});

test('expands groups for print restores their state and invokes printing', () => {
    const {window} = boot();
    const {document} = window;
    const groups = [...document.querySelectorAll('[data-settings-group]')];
    groups[0].open = true; groups[1].open = false;
    const actions = document.querySelector('.settings-more-actions'); actions.open = true;
    window.dispatchEvent(new window.Event('beforeprint'));
    assert.equal(actions.open, false);
    assert.equal(groups.every((group) => group.open), true);
    assert.notEqual(document.getElementById('settings-print-generated').textContent, '');
    assert.equal(document.getElementById('settings-print-url').textContent, window.location.href);
    window.dispatchEvent(new window.Event('afterprint'));
    assert.equal(groups[0].open, true);
    assert.equal(groups[1].open, false);
    document.getElementById('settings-print').click();
    assert.equal(window.__printed, true);
});
