(() => {
    'use strict';

    const workspace = document.querySelector('[data-grid-workspace]');
    if (!workspace) return;

    const tables = Array.from(document.querySelectorAll('table'));
    const table = tables.find((candidate) => {
        const first = candidate.querySelector('thead th');
        return first && /^id(?:\s*[▲▼])?$/i.test(first.textContent.trim());
    });
    if (!table || table.dataset.gridSelectionBound === 'true') return;

    const headerRow = table.querySelector('thead tr');
    const body = table.querySelector('tbody');
    if (!headerRow || !body) return;

    const rows = Array.from(body.querySelectorAll(':scope > tr'));
    const identities = rows.map((row) => row.querySelector(':scope > td')?.textContent.trim() ?? '');
    const usable = identities.length > 0
        && identities.every((value) => value !== '')
        && new Set(identities).size === identities.length;
    if (!usable) return;

    table.dataset.gridSelectionBound = 'true';
    table.classList.add('grid-has-selection');

    const selectAllCell = document.createElement('th');
    selectAllCell.className = 'grid-selection-cell';
    selectAllCell.scope = 'col';
    const selectAll = document.createElement('input');
    selectAll.type = 'checkbox';
    selectAll.setAttribute('aria-label', 'Select all rows on this page');
    selectAllCell.append(selectAll);
    headerRow.prepend(selectAllCell);

    const selectionBar = document.createElement('div');
    selectionBar.className = 'grid-selection-bar';
    selectionBar.dataset.gridSelectionBar = '';
    selectionBar.hidden = true;
    selectionBar.setAttribute('role', 'status');
    selectionBar.setAttribute('aria-live', 'polite');

    const count = document.createElement('strong');
    count.dataset.gridSelectionCount = '';
    const action = document.createElement('select');
    action.name = 'bulk_action';
    action.disabled = true;
    action.setAttribute('aria-label', 'Bulk actions');
    action.innerHTML = '<option>Bulk actions</option>';
    const clear = document.createElement('button');
    clear.type = 'button';
    clear.textContent = 'Clear selection';
    selectionBar.append(count, action, clear);
    workspace.insertAdjacentElement('afterend', selectionBar);

    const selected = new Set();
    const checkboxes = [];

    const update = () => {
        const size = selected.size;
        count.textContent = `${size} ${size === 1 ? 'row' : 'rows'} selected`;
        selectionBar.hidden = size === 0;
        action.disabled = size === 0;
        selectAll.checked = size === checkboxes.length && size > 0;
        selectAll.indeterminate = size > 0 && size < checkboxes.length;
    };

    rows.forEach((row, index) => {
        const identity = identities[index];
        row.dataset.gridRowId = identity;
        const cell = document.createElement('td');
        cell.className = 'grid-selection-cell';
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'selected_ids[]';
        checkbox.value = identity;
        checkbox.setAttribute('aria-label', `Select row ${identity}`);
        checkbox.addEventListener('change', () => {
            checkbox.checked ? selected.add(identity) : selected.delete(identity);
            row.classList.toggle('is-selected', checkbox.checked);
            update();
        });
        cell.append(checkbox);
        row.prepend(cell);
        checkboxes.push(checkbox);
    });

    selectAll.addEventListener('change', () => {
        checkboxes.forEach((checkbox, index) => {
            checkbox.checked = selectAll.checked;
            const identity = identities[index];
            selectAll.checked ? selected.add(identity) : selected.delete(identity);
            rows[index].classList.toggle('is-selected', selectAll.checked);
        });
        update();
    });

    clear.addEventListener('click', () => {
        selected.clear();
        checkboxes.forEach((checkbox, index) => {
            checkbox.checked = false;
            rows[index].classList.remove('is-selected');
        });
        update();
        checkboxes[0]?.focus();
    });
})();
