(() => {
    'use strict';

    const bind = () => {
        const manifestNode = document.querySelector('[data-grid-bulk-action-manifest]');
        const action = document.querySelector('[data-grid-selection-bar] select[aria-label="Bulk actions"]');
        if (!manifestNode || !action || action.dataset.gridManifestBound === 'true') return;

        let manifest;
        try {
            manifest = JSON.parse(manifestNode.textContent || '{}');
        } catch {
            return;
        }
        if (!Array.isArray(manifest.actions)) return;

        const supported = manifest.actions.filter((definition) =>
            definition
            && definition.executionType === 'client_download'
            && definition.selectionScope === 'explicit_identities'
            && definition.id === 'export.selected'
        );

        action.dataset.gridManifestBound = 'true';
        action.replaceChildren(new Option('Bulk actions', ''));
        supported.forEach((definition) => {
            action.add(new Option(String(definition.label), String(definition.id)));
        });
    };

    bind();
    new MutationObserver(bind).observe(document.body, {childList: true, subtree: true});
})();
