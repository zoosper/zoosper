(() => {
    'use strict';

    const margin = 16;
    const gap = 8;

    const locate = (trigger) => {
        const workspace = trigger.closest('[data-grid-workspace]');
        if (!workspace) return null;
        return workspace.querySelector(`[data-grid-panel="${trigger.dataset.gridToggle}"]`);
    };

    const position = (trigger, panel) => {
        if (panel.hidden || window.matchMedia('(max-width: 720px)').matches) {
            panel.removeAttribute('style');
            return;
        }

        const triggerBox = trigger.getBoundingClientRect();
        const workspaceBox = trigger.closest('[data-grid-workspace]')?.getBoundingClientRect();
        const boundaryLeft = Math.max(margin, workspaceBox?.left ?? margin);
        const boundaryRight = Math.min(
            window.innerWidth - margin,
            workspaceBox?.right ?? window.innerWidth - margin,
        );
        const available = Math.max(320, boundaryRight - boundaryLeft);
        const preferredWidth = trigger.dataset.gridToggle === 'filters' ? 760 : 420;
        const width = Math.min(preferredWidth, available);
        const centredLeft = triggerBox.left + (triggerBox.width / 2) - (width / 2);
        const left = Math.min(
            Math.max(boundaryLeft, centredLeft),
            boundaryRight - width,
        );

        panel.style.setProperty('--grid-command-panel-left', `${Math.max(boundaryLeft, left)}px`);
        panel.style.setProperty('--grid-command-panel-top', `${triggerBox.bottom + gap}px`);
        panel.style.setProperty('--grid-command-panel-width', `${width}px`);
    };

    const positionOpenPanels = () => {
        document.querySelectorAll('[data-grid-toggle]').forEach((trigger) => {
            const panel = locate(trigger);
            if (panel && !panel.hidden) position(trigger, panel);
        });
    };

    document.querySelectorAll('[data-grid-toggle]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const panel = locate(trigger);
            if (!panel) return;
            requestAnimationFrame(() => position(trigger, panel));
        });
    });

    window.addEventListener('resize', positionOpenPanels);
    window.addEventListener('scroll', positionOpenPanels, true);
})();
