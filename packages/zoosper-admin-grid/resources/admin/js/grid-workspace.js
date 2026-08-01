(() => {
    'use strict';

    const setExpanded = (button, panel, expanded) => {
        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        panel.hidden = !expanded;
    };

    const syncOrderInputs = (list) => {
        list.querySelectorAll('[data-column-key]').forEach((item) => {
            const input = item.querySelector('input[name="column_order[]"]');
            if (input) {
                input.value = item.dataset.columnKey || '';
            }
        });
    };

    const moveItem = (item, direction) => {
        const sibling = direction === 'up' ? item.previousElementSibling : item.nextElementSibling;
        if (!sibling) {
            return;
        }
        if (direction === 'up') {
            item.parentElement.insertBefore(item, sibling);
        } else {
            item.parentElement.insertBefore(sibling, item);
        }
        syncOrderInputs(item.parentElement);
        item.focus({preventScroll: true});
    };

    const initialiseWorkspace = (workspace) => {
        workspace.querySelectorAll('[data-grid-panel-toggle]').forEach((button) => {
            const panel = workspace.querySelector(`[data-grid-panel="${button.dataset.gridPanelToggle}"]`);
            if (!panel) {
                return;
            }
            button.addEventListener('click', () => {
                setExpanded(button, panel, button.getAttribute('aria-expanded') !== 'true');
            });
        });

        const list = workspace.querySelector('[data-grid-column-list]');
        if (!list) {
            return;
        }

        list.addEventListener('click', (event) => {
            const button = event.target.closest('[data-column-move]');
            if (!button) {
                return;
            }
            const item = button.closest('[data-column-key]');
            if (item) {
                moveItem(item, button.dataset.columnMove);
            }
        });

        let dragged = null;
        list.addEventListener('dragstart', (event) => {
            dragged = event.target.closest('[data-column-key]');
            if (dragged) {
                dragged.classList.add('is-dragging');
                event.dataTransfer.effectAllowed = 'move';
            }
        });
        list.addEventListener('dragover', (event) => {
            const target = event.target.closest('[data-column-key]');
            if (!dragged || !target || dragged === target) {
                return;
            }
            event.preventDefault();
            const box = target.getBoundingClientRect();
            const after = event.clientY > box.top + box.height / 2;
            list.insertBefore(dragged, after ? target.nextElementSibling : target);
        });
        list.addEventListener('dragend', () => {
            if (dragged) {
                dragged.classList.remove('is-dragging');
                dragged.focus({preventScroll: true});
            }
            dragged = null;
            syncOrderInputs(list);
        });

        syncOrderInputs(list);
    };

    document.querySelectorAll('[data-grid-workspace]').forEach(initialiseWorkspace);
})();
