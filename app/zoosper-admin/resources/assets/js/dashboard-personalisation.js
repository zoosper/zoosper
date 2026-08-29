(() => {
    'use strict';

    const root = document.querySelector('[data-dashboard-personalisation]');
    const form = root?.querySelector('[data-dashboard-personalisation-form]');
    const orderList = root?.querySelector('[data-dashboard-widget-order]');
    const grid = document.querySelector('[data-dashboard-widget-grid]');
    if (!(root instanceof HTMLDetailsElement) || !(form instanceof HTMLFormElement) || !(orderList instanceof HTMLOListElement)) {
        return;
    }

    const status = root.querySelector('[data-dashboard-order-status]');
    const visibleCount = document.querySelector('[data-dashboard-visible-count]');
    const hiddenEmpty = document.querySelector('[data-dashboard-hidden-empty]');
    let draggedCode = null;

    const items = () => Array.from(orderList.querySelectorAll('[data-dashboard-order-item]'));
    const codeOf = (element) => element instanceof HTMLElement ? element.dataset.dashboardOrderItem ?? null : null;
    const cardFor = (code) => grid?.querySelector(`[data-dashboard-widget="${CSS.escape(code)}"]`);

    const announce = (message) => {
        if (status instanceof HTMLElement) {
            status.textContent = message;
        }
    };

    const sync = () => {
        items().forEach((item) => {
            const code = codeOf(item);
            const input = item.querySelector('[data-dashboard-order-input]');
            const checkbox = item.querySelector('[data-dashboard-visibility]');
            if (code === null || !(input instanceof HTMLInputElement) || !(checkbox instanceof HTMLInputElement)) {
                return;
            }
            input.value = code;
            const card = cardFor(code);
            if (card instanceof HTMLElement && grid instanceof HTMLElement) {
                card.hidden = !checkbox.checked;
                grid.append(card);
            }
        });

        const count = items().filter((item) => {
            const checkbox = item.querySelector('[data-dashboard-visibility]');
            return checkbox instanceof HTMLInputElement && checkbox.checked;
        }).length;
        if (visibleCount instanceof HTMLElement) {
            visibleCount.textContent = String(count);
        }
        if (hiddenEmpty instanceof HTMLElement) {
            hiddenEmpty.hidden = count !== 0;
        }
    };

    const move = (item, direction) => {
        if (!(item instanceof HTMLElement)) {
            return;
        }
        const sibling = direction === 'up' ? item.previousElementSibling : item.nextElementSibling;
        if (!(sibling instanceof HTMLElement)) {
            return;
        }
        if (direction === 'up') {
            orderList.insertBefore(item, sibling);
        } else {
            orderList.insertBefore(sibling, item);
        }
        sync();
        const label = item.querySelector('.dashboard-personalisation__visibility span')?.textContent?.trim() ?? 'Widget';
        announce(`${label} moved ${direction}. Save the layout to keep this order.`);
        item.querySelector(`[data-dashboard-move="${direction}"]`)?.focus();
    };

    orderList.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }
        const button = target.closest('[data-dashboard-move]');
        const item = button?.closest('[data-dashboard-order-item]');
        const direction = button instanceof HTMLElement ? button.dataset.dashboardMove : null;
        if (direction === 'up' || direction === 'down') {
            move(item, direction);
        }
    });

    orderList.addEventListener('change', (event) => {
        if (event.target instanceof HTMLInputElement && event.target.matches('[data-dashboard-visibility]')) {
            sync();
            announce(`${event.target.checked ? 'Shown' : 'Hidden'} on this page. Save the layout to keep the change.`);
        }
    });

    const beginDrag = (event, code) => {
        draggedCode = code;
        event.dataTransfer?.setData('text/plain', code);
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
        }
    };

    orderList.addEventListener('dragstart', (event) => {
        const handle = event.target instanceof Element ? event.target.closest('[data-dashboard-drag-handle]') : null;
        const code = codeOf(handle?.closest('[data-dashboard-order-item]'));
        if (code === null) {
            event.preventDefault();
            return;
        }
        beginDrag(event, code);
    });
    orderList.addEventListener('dragover', (event) => {
        if (draggedCode !== null && event.target instanceof Element && event.target.closest('[data-dashboard-order-item]')) {
            event.preventDefault();
        }
    });
    orderList.addEventListener('drop', (event) => {
        const target = event.target instanceof Element ? event.target.closest('[data-dashboard-order-item]') : null;
        const dragged = draggedCode === null ? null : orderList.querySelector(`[data-dashboard-order-item="${CSS.escape(draggedCode)}"]`);
        if (!(target instanceof HTMLElement) || !(dragged instanceof HTMLElement) || target === dragged) {
            draggedCode = null;
            return;
        }
        event.preventDefault();
        orderList.insertBefore(dragged, target);
        announce('Widget order changed. Save the layout to keep this order.');
        draggedCode = null;
        sync();
    });

    grid?.addEventListener('dragstart', (event) => {
        const handle = event.target instanceof Element ? event.target.closest('[data-dashboard-card-drag]') : null;
        const card = handle?.closest('[data-dashboard-widget]');
        const code = card instanceof HTMLElement ? card.dataset.dashboardWidget ?? null : null;
        if (code === null) {
            event.preventDefault();
            return;
        }
        beginDrag(event, code);
    });
    grid?.addEventListener('dragover', (event) => {
        if (draggedCode !== null && event.target instanceof Element && event.target.closest('[data-dashboard-widget]')) {
            event.preventDefault();
        }
    });
    grid?.addEventListener('drop', (event) => {
        const targetCard = event.target instanceof Element ? event.target.closest('[data-dashboard-widget]') : null;
        const targetCode = targetCard instanceof HTMLElement ? targetCard.dataset.dashboardWidget ?? null : null;
        const draggedItem = draggedCode === null ? null : orderList.querySelector(`[data-dashboard-order-item="${CSS.escape(draggedCode)}"]`);
        const targetItem = targetCode === null ? null : orderList.querySelector(`[data-dashboard-order-item="${CSS.escape(targetCode)}"]`);
        if (!(draggedItem instanceof HTMLElement) || !(targetItem instanceof HTMLElement) || draggedItem === targetItem) {
            draggedCode = null;
            return;
        }
        event.preventDefault();
        orderList.insertBefore(draggedItem, targetItem);
        root.open = true;
        announce('Widget order changed. Save the layout to keep this order.');
        draggedCode = null;
        sync();
    });

    document.addEventListener('dragend', () => {
        draggedCode = null;
    });
    sync();
})();
