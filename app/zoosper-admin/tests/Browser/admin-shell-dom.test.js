import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const source = await readFile(new URL('../../resources/assets/js/admin-shell.js', import.meta.url), 'utf8');
const fixture = () => `
<div data-admin-shell>
  <aside data-admin-sidebar id="admin-navigation">
    <a href="/admin">Dashboard</a>
    <button type="button" data-admin-sidebar-toggle aria-pressed="false"><span data-admin-collapse-icon>‹</span><span class="admin-control-label">Collapse navigation</span></button>
  </aside>
  <button type="button" data-admin-navigation-close aria-hidden="true"></button>
  <button type="button" data-admin-navigation-toggle aria-expanded="false"><span class="admin-control-label">Open navigation</span></button>
  <select data-admin-theme-selector><option value="light" data-admin-theme-mode="light">Light</option><option value="dark" data-admin-theme-mode="dark">Dark</option><option value="ocean" data-admin-theme-mode="dark">Ocean</option></select>
</div>`;

const boot = ({mobile = false, dark = false, stored = {}} = {}) => {
    const dom = new JSDOM(fixture(), {runScripts: 'outside-only', url: 'https://example.test/admin'});
    const listeners = new Map();
    dom.window.matchMedia = (query) => {
        const record = {matches: query.includes('max-width') ? mobile : dark, addEventListener: (_type, listener) => listeners.set(query, listener)};
        return record;
    };
    Object.entries(stored).forEach(([key, value]) => dom.window.localStorage.setItem(key, value));
    dom.window.eval(source);
    return {dom, listeners};
};

test('restores and persists theme and sidebar preferences', () => {
    const {dom} = boot({stored: {'zoosper.admin.theme': 'ocean', 'zoosper.admin.sidebar-collapsed': 'true'}});
    const {document, Event, localStorage} = dom.window;
    const shell = document.querySelector('[data-admin-shell]');
    const sidebarToggle = document.querySelector('[data-admin-sidebar-toggle]');
    const selector = document.querySelector('[data-admin-theme-selector]');
    assert.equal(document.documentElement.dataset.adminTheme, 'dark');
    assert.equal(document.documentElement.dataset.adminThemePalette, 'ocean');
    assert.equal(selector.value, 'ocean');
    assert.equal(shell.dataset.sidebarCollapsed, 'true');
    assert.equal(sidebarToggle.getAttribute('aria-pressed'), 'true');
    assert.equal(sidebarToggle.getAttribute('aria-label'), 'Expand navigation');
    assert.equal(sidebarToggle.querySelector('[data-admin-collapse-icon]').textContent, '›');
    sidebarToggle.click();
    assert.equal(shell.dataset.sidebarCollapsed, 'false');
    assert.equal(localStorage.getItem('zoosper.admin.sidebar-collapsed'), 'false');
    selector.value = 'light';
    selector.dispatchEvent(new Event('change', {bubbles: true}));
    assert.equal(document.documentElement.dataset.adminTheme, 'light');
    assert.equal(localStorage.getItem('zoosper.admin.theme'), 'light');
});

test('opens mobile navigation, traps focus and restores the trigger on Escape', () => {
    const {dom} = boot({mobile: true});
    const {document, KeyboardEvent} = dom.window;
    const shell = document.querySelector('[data-admin-shell]');
    const toggle = document.querySelector('[data-admin-navigation-toggle]');
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const first = sidebar.querySelector('a');
    const last = sidebar.querySelector('button');
    toggle.click();
    assert.equal(shell.classList.contains('is-navigation-open'), true);
    assert.equal(document.body.classList.contains('admin-navigation-active'), true);
    assert.equal(toggle.getAttribute('aria-expanded'), 'true');
    assert.equal(sidebar.hasAttribute('inert'), false);
    last.focus();
    document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Tab', bubbles: true}));
    assert.equal(document.activeElement, first);
    document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape', bubbles: true}));
    assert.equal(shell.classList.contains('is-navigation-open'), false);
    assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    assert.equal(document.activeElement, toggle);
    assert.equal(sidebar.hasAttribute('inert'), true);
});

test('closes mobile navigation when the breakpoint changes', () => {
    const {dom, listeners} = boot({mobile: true});
    const {document} = dom.window;
    const shell = document.querySelector('[data-admin-shell]');
    document.querySelector('[data-admin-navigation-toggle]').click();
    assert.equal(shell.classList.contains('is-navigation-open'), true);
    listeners.get('(max-width: 860px)')();
    assert.equal(shell.classList.contains('is-navigation-open'), false);
});
