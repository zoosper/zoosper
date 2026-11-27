import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const source = await readFile(new URL('../../resources/admin/js/email-log-workspace.js', import.meta.url), 'utf8');

test('preserves shared grid pagination links and page controls', () => {
    const dom = new JSDOM(`<div data-email-logs-page><div data-grid-workspace></div>
        <table class="grid-table"></table>
        <nav class="grid-pagination"><a class="grid-pagination__prev" href="?page=1">&laquo; Previous</a>
        <span class="grid-pagination__status grid-pagination-controls"><form data-grid-page-jump></form></span>
        <a class="grid-pagination__next" href="?page=3">Next &raquo;</a></nav></div>`, {runScripts: 'outside-only'});
    dom.window.eval(source);
    const footer = dom.window.document.querySelector('[data-email-logs-pagination]');
    assert.equal(footer.querySelector('.email-logs-index__previous').getAttribute('href'), '?page=1');
    assert.equal(footer.querySelector('.email-logs-index__next').getAttribute('href'), '?page=3');
    assert.ok(footer.querySelector('[data-grid-page-jump]'));
    assert.equal(dom.window.document.querySelector('.grid-pagination'), null);
});

test('keeps unavailable directions disabled and enhancement idempotent', () => {
    const dom = new JSDOM(`<div data-email-logs-page><div data-grid-workspace></div>
        <table class="grid-table"></table>
        <nav class="grid-pagination"><span class="grid-pagination__prev grid-pagination__disabled">Previous</span>
        <span class="grid-pagination-controls"></span>
        <a class="grid-pagination__next" href="?page=2">Next</a></nav></div>`, {runScripts: 'outside-only'});
    dom.window.eval(source);
    dom.window.eval(source);
    const document = dom.window.document;
    const footer = document.querySelector('[data-email-logs-pagination]');
    assert.equal(footer.querySelector('.email-logs-index__previous').getAttribute('aria-disabled'), 'true');
    assert.equal(footer.querySelector('.email-logs-index__next').getAttribute('href'), '?page=2');
    assert.equal(document.querySelectorAll('[data-email-logs-pagination]').length, 1);
});