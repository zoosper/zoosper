import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const source = await readFile(new URL('../../resources/assets/admin/js/permission-explorer.js', import.meta.url), 'utf8');
const permission = (code, label, checked = false, disabled = false) => `<label><input type="checkbox" name="permission_ids[]" value="${code}" ${checked ? 'checked' : ''} ${disabled ? 'disabled' : ''}><code>${code}</code> ${label}</label>`;
const fixture = ({explicitRoot = true} = {}) => `<form id="role-form" ${explicitRoot ? 'data-permission-tree' : ''}>
  <fieldset><legend>Content</legend>${permission('page.read', 'Read pages', true)}${permission('page.write', 'Write pages')}</fieldset>
  <fieldset><legend>Security</legend>${permission('role.manage', 'Manage roles')}${permission('locked.permission', 'Locked permission', false, true)}</fieldset>
</form>`;
const boot = (options = {}) => {
    const dom = new JSDOM(fixture(options), {
        runScripts: 'outside-only',
        url: 'https://example.test/admin/roles/create',
    });

    dom.window.eval(source);
    dom.window.document.dispatchEvent(
        new dom.window.Event('DOMContentLoaded', {bubbles: true}),
    );

    return dom;
};
const action = (document, name) => document.querySelector(`[data-action="${name}"]`);

test('discovers and enhances the existing role form with accessible controls', () => {
    const dom = boot({explicitRoot: false});
    const {document} = dom.window;
    const form = document.querySelector('form');
    assert.equal(form.dataset.permissionExplorerBound, 'true');
    assert.equal(form.classList.contains('permission-explorer'), true);
    assert.equal(document.querySelector('.permission-explorer__search').placeholder, 'Code or permission name');
    assert.equal(document.querySelector('.permission-explorer__count').getAttribute('aria-live'), 'polite');
    assert.equal(document.querySelector('.permission-explorer__count').textContent, '1 of 4 selected');
    assert.deepEqual(Array.from(document.querySelectorAll('[data-action]'), (button) => button.textContent), ['Expand all', 'Collapse all', 'Select visible', 'Clear visible']);
});

test('filters rows and groups and clears search with Escape', () => {
    const dom = boot();
    const {document, Event, KeyboardEvent} = dom.window;
    const search = document.querySelector('.permission-explorer__search');
    const groups = document.querySelectorAll('fieldset');
    search.value = 'roles';
    search.dispatchEvent(new Event('input', {bubbles: true}));
    assert.equal(groups[0].hidden, true);
    assert.equal(groups[1].hidden, false);
    assert.equal(groups[1].querySelector('[data-group-toggle]').getAttribute('aria-expanded'), 'true');
    search.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape', bubbles: true}));
    assert.equal(search.value, '');
    assert.equal(Array.from(groups).every((group) => !group.hidden), true);
});

test('collapses expands and selects only visible enabled permissions', () => {
    const dom = boot();
    const {document, Event} = dom.window;
    const search = document.querySelector('.permission-explorer__search');
    const groups = document.querySelectorAll('fieldset');
    action(document, 'collapse').click();
    assert.equal(Array.from(groups).every((group) => group.classList.contains('permission-explorer__group--collapsed')), true);
    assert.equal(Array.from(groups).every((group) => group.querySelector('[data-group-toggle]').getAttribute('aria-expanded') === 'false'), true);
    action(document, 'expand').click();
    assert.equal(Array.from(groups).every((group) => !group.classList.contains('permission-explorer__group--collapsed')), true);
    search.value = 'roles';
    search.dispatchEvent(new Event('input', {bubbles: true}));
    action(document, 'select-visible').click();
    assert.equal(document.querySelector('input[value="role.manage"]').checked, true);
    assert.equal(document.querySelector('input[value="locked.permission"]').checked, false);
    assert.equal(document.querySelector('input[value="page.write"]').checked, false);
    assert.equal(document.querySelector('.permission-explorer__count').textContent, '2 of 4 selected');
    action(document, 'clear-visible').click();
    assert.equal(document.querySelector('input[value="role.manage"]').checked, false);
    assert.equal(document.querySelector('input[value="page.read"]').checked, true);
});

test('updates selected count when a permission changes and binds only once', () => {
    const dom = boot();
    const {document, Event} = dom.window;
    const form = document.querySelector('form');
    const checkbox = document.querySelector('input[value="page.write"]');
    checkbox.checked = true;
    checkbox.dispatchEvent(new Event('change', {bubbles: true}));
    assert.equal(document.querySelector('.permission-explorer__count').textContent, '2 of 4 selected');
    dom.window.eval(source);
    assert.equal(form.querySelectorAll('.permission-explorer__toolbar').length, 1);
});
