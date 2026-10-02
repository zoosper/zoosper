import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import {JSDOM} from 'jsdom';

const source = await readFile(new URL('../../resources/admin/js/page-grid-search.js', import.meta.url), 'utf8');
const fixture = () => `<section class="page-grid-index">
<div data-grid-workspace><div class="grid-compact-actions"></div><form data-grid-filter-form><label>Query <input name="q"></label><input name="page" value="4"></form></div>
<table class="grid-table"><thead><tr><th data-grid-column="title"><a>Title</a></th><th data-grid-column="slug">Slug</th></tr></thead><tbody><tr><td data-grid-column="title">About</td><td data-grid-column="slug">about</td><td data-grid-column="status">published</td><td data-grid-column="site_name">Main</td><td data-grid-column="actions"><a>Edit</a></td></tr></tbody></table>
<div>Showing 1 item</div><nav class="grid-workspace__navigation" aria-label="Pagination"><a rel="prev">Previous</a><div class="grid-pagination-controls"><a rel="next">Next</a></div></nav></section>`;
const boot = () => { const dom=new JSDOM(fixture(),{runScripts:'outside-only',url:'https://example.test/admin/pages'}); const form=dom.window.document.querySelector('[data-grid-filter-form]'); form.requestSubmit=()=>{form.dataset.submitted='true';}; dom.window.eval(source); return dom; };

test('promotes the canonical query control and resets pagination on Enter',()=>{const {window}=boot(),d=window.document,q=d.querySelector('[name="q"]'),form=d.querySelector('[data-grid-filter-form]');assert.equal(q.type,'search');assert.equal(q.placeholder,'Search pages by title or slug');assert.equal(q.getAttribute('form'),'page-grid-filter-form');q.dispatchEvent(new window.KeyboardEvent('keydown',{key:'Enter',bubbles:true,cancelable:true}));assert.equal(d.querySelector('[name="page"]').value,'1');assert.equal(form.dataset.submitted,'true');});
test('enhances Page identity status site and actions idempotently',()=>{const d=boot().window.document,row=d.querySelector('tbody tr');assert.equal(row.dataset.pageGridRowEnhanced,'true');assert.equal(d.querySelector('.page-grid-index__slug').textContent,'/about');assert.equal(d.querySelector('[data-grid-column="status"] .page-grid-index__status--published').textContent,'published');assert.ok(d.querySelector('[data-grid-column="site_name"] .page-grid-index__site-dot'));assert.equal(d.querySelector('[data-grid-column="actions"] a').classList.contains('page-grid-index__row-action'),true);assert.equal(d.querySelectorAll('.page-grid-index__slug').length,1);});
test('composes existing pagination controls into one Page-owned footer',()=>{const d=boot().window.document,footer=d.querySelector('[data-page-grid-pagination]');assert.ok(footer);assert.ok(footer.querySelector('.grid-pagination-controls'));assert.ok(footer.querySelector('[rel="prev"]'));assert.ok(footer.querySelector('[rel="next"]'));assert.equal(d.querySelector('.grid-workspace__navigation'),null);});
