(() => {
    'use strict';

    const settings = document.querySelector('[data-grid-settings]');
    const settingsToggle = document.querySelector('[data-grid-settings-toggle]');
    const settingsClose = settings?.querySelector('[data-grid-settings-close]');

    // Pages may render the shared toolbar without mutation forms. Do not show a
    // control that has no target on that page.
    if (!settings) {
        if (settingsToggle) settingsToggle.hidden = true;
        return;
    }

    const closeSettings = (restoreFocus = false) => {
        settings.hidden = true;
        settings.open = false;
        settings.removeAttribute('style');
        settingsToggle?.setAttribute('aria-expanded', 'false');
        if (restoreFocus) settingsToggle?.focus();
    };

    const positionSettings = () => {
        if (!settingsToggle || settings.hidden) return;
        const trigger = settingsToggle.getBoundingClientRect();
        const margin = 16;
        const gap = 8;
        const width = Math.min(680, Math.max(320, window.innerWidth - (margin * 2)));
        const left = Math.min(
            Math.max(margin, trigger.right - width),
            window.innerWidth - width - margin,
        );
        settings.style.setProperty('--grid-settings-left', `${left}px`);
        settings.style.setProperty('--grid-settings-top', `${trigger.bottom + gap}px`);
        settings.style.setProperty('--grid-settings-width', `${width}px`);
    };

    settingsToggle?.addEventListener('click', () => {
        const willOpen = settings.hidden;
        if (!willOpen) {
            closeSettings(true);
            return;
        }
        // Let the established compact-workspace script continue to own Filters
        // and Columns. This script owns only saved-view management.
        document.querySelectorAll('[data-grid-panel]').forEach((panel) => {
            panel.hidden = true;
        });
        document.querySelectorAll('[data-grid-toggle]').forEach((button) => {
            button.setAttribute('aria-expanded', 'false');
        });
        settings.hidden = false;
        settings.open = true;
        settingsToggle.setAttribute('aria-expanded', 'true');
        positionSettings();
        settings.querySelector('input[name="view_name"]')?.focus();
    });

    settingsClose?.addEventListener('click', () => closeSettings(true));
    window.addEventListener('resize', positionSettings);
    window.addEventListener('scroll', positionSettings, true);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !settings.hidden) closeSettings(true);
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Node)) return;
        if (settings.contains(target) || settingsToggle?.contains(target)) return;
        closeSettings();
    });
})();
