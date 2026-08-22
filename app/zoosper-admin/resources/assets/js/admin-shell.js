(() => {
    'use strict';

    const shell = document.querySelector('[data-admin-shell]');
    if (!(shell instanceof HTMLElement)) {
        return;
    }

    const root = document.documentElement;
    const sidebar = shell.querySelector('[data-admin-sidebar]');
    const navigationToggle = shell.querySelector('[data-admin-navigation-toggle]');
    const navigationClose = shell.querySelector('[data-admin-navigation-close]');
    const sidebarToggle = shell.querySelector('[data-admin-sidebar-toggle]');
    const themeToggle = shell.querySelector('[data-admin-theme-toggle]');
    const themeLabel = shell.querySelector('[data-admin-theme-label]');
    const mobileQuery = window.matchMedia('(max-width: 860px)');
    const darkQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const themeKey = 'zoosper.admin.theme';
    const sidebarKey = 'zoosper.admin.sidebar-collapsed';

    shell.dataset.adminShellReady = 'true';

    const storageGet = (key) => {
        try {
            return window.localStorage.getItem(key);
        } catch (_error) {
            return null;
        }
    };

    const storageSet = (key, value) => {
        try {
            window.localStorage.setItem(key, value);
        } catch (_error) {
            // Storage can be disabled without disabling the shell controls.
        }
    };

    const setTheme = (theme, persist = false) => {
        const resolved = theme === 'dark' ? 'dark' : 'light';
        root.dataset.adminTheme = resolved;
        if (themeToggle instanceof HTMLButtonElement) {
            themeToggle.setAttribute('aria-pressed', resolved === 'dark' ? 'true' : 'false');
            themeToggle.title = resolved === 'dark' ? 'Use light theme' : 'Use dark theme';
        }
        if (themeLabel instanceof HTMLElement) {
            themeLabel.textContent = resolved === 'dark' ? 'Use light theme' : 'Use dark theme';
        }
        if (persist) {
            storageSet(themeKey, resolved);
        }
    };

    const storedTheme = storageGet(themeKey);
    setTheme(storedTheme === 'light' || storedTheme === 'dark'
        ? storedTheme
        : (darkQuery.matches ? 'dark' : 'light'));

    themeToggle?.addEventListener('click', () => {
        setTheme(root.dataset.adminTheme === 'dark' ? 'light' : 'dark', true);
    });

    darkQuery.addEventListener('change', (event) => {
        if (storageGet(themeKey) === null) {
            setTheme(event.matches ? 'dark' : 'light');
        }
    });

    const setCollapsed = (collapsed, persist = false) => {
        shell.dataset.sidebarCollapsed = collapsed ? 'true' : 'false';
        if (sidebarToggle instanceof HTMLButtonElement) {
            sidebarToggle.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            sidebarToggle.title = collapsed ? 'Expand navigation' : 'Collapse navigation';
            const label = sidebarToggle.querySelector('.admin-control-label');
            if (label instanceof HTMLElement) {
                label.textContent = collapsed ? 'Expand navigation' : 'Collapse navigation';
            }
        }
        if (persist) {
            storageSet(sidebarKey, collapsed ? 'true' : 'false');
        }
    };

    setCollapsed(storageGet(sidebarKey) === 'true');
    sidebarToggle?.addEventListener('click', () => {
        setCollapsed(shell.dataset.sidebarCollapsed !== 'true', true);
    });

    const focusableNavigation = () => {
        if (!(sidebar instanceof HTMLElement)) {
            return [];
        }

        return Array.from(sidebar.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])'))
            .filter((element) => element instanceof HTMLElement && !element.hidden);
    };

    const setNavigationOpen = (open, restoreFocus = false) => {
        const shouldOpen = open && mobileQuery.matches;
        shell.classList.toggle('is-navigation-open', shouldOpen);
        document.body.classList.toggle('admin-navigation-active', shouldOpen);
        navigationToggle?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        navigationClose?.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
        if (sidebar instanceof HTMLElement) {
            sidebar.toggleAttribute('inert', mobileQuery.matches && !shouldOpen);
        }

        const toggleLabel = navigationToggle?.querySelector('.admin-control-label');
        if (toggleLabel instanceof HTMLElement) {
            toggleLabel.textContent = shouldOpen ? 'Close navigation' : 'Open navigation';
        }

        if (shouldOpen) {
            const first = focusableNavigation()[0];
            if (first instanceof HTMLElement) {
                first.focus();
            }
        } else if (restoreFocus && navigationToggle instanceof HTMLElement) {
            navigationToggle.focus();
        }
    };

    navigationToggle?.addEventListener('click', () => {
        setNavigationOpen(!shell.classList.contains('is-navigation-open'), true);
    });
    navigationClose?.addEventListener('click', () => setNavigationOpen(false, true));

    sidebar?.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', () => setNavigationOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (!shell.classList.contains('is-navigation-open')) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            setNavigationOpen(false, true);
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const focusable = focusableNavigation();
        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });

    mobileQuery.addEventListener('change', () => setNavigationOpen(false));
    setNavigationOpen(false);
})();
