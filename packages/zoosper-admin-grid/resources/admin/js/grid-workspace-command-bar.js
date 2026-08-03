(() => {
    'use strict';

    const workspace = document.querySelector('[data-grid-workspace]');
    if (!workspace) return;

    const panels = Array.from(workspace.querySelectorAll('[data-grid-panel]'));
    const toggles = Array.from(document.querySelectorAll('[data-grid-toggle]'));
    const settings = document.querySelector('[data-grid-settings]');
    const settingsToggle = document.querySelector('[data-grid-settings-toggle]');

    const closePanels = (except = null) => {
        panels.forEach((panel) => {
            if (panel === except) return;
            panel.hidden = true;
        });
        toggles.forEach((button) => {
            const target = workspace.querySelector(`[data-grid-panel="${button.dataset.gridToggle}"]`);
            if (target !== except) button.setAttribute('aria-expanded', 'false');
        });
    };

    toggles.forEach((button) => {
        if (button.dataset.gridCommandBarBound === 'true') return;
        button.dataset.gridCommandBarBound = 'true';
        button.addEventListener('click', () => {
            const panel = workspace.querySelector(`[data-grid-panel="${button.dataset.gridToggle}"]`);
            if (!panel) return;
            const willOpen = panel.hidden;
            closePanels(panel);
            if (settings) {
                settings.hidden = true;
                settings.open = false;
            }
            panel.hidden = !willOpen;
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    settingsToggle?.addEventListener('click', () => {
        closePanels();
        if (!settings) return;
        const willOpen = settings.hidden;
        settings.hidden = !willOpen;
        settings.open = willOpen;
        settingsToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        if (willOpen) settings.querySelector('input[name="view_name"]')?.focus();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closePanels();
        if (settings) {
            settings.hidden = true;
            settings.open = false;
        }
        settingsToggle?.setAttribute('aria-expanded', 'false');
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Node)) return;
        if (workspace.contains(target) || settings?.contains(target) || settingsToggle?.contains(target)) return;
        closePanels();
    });
})();
