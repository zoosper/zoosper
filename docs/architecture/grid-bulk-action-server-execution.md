# Grid bulk-action server execution boundary

`zoosper-grid` now defines a framework-neutral server execution boundary. `GridBulkActionDispatcher` resolves the registered definition, rejects client-only actions, requires explicit identities, validates and de-duplicates the selection, enforces the declared maximum, resolves the exact Grid/action executor and delegates.

Feature modules implement `GridBulkActionExecutorInterface`; core does not know repositories, remote APIs or domain rules. The dispatcher does not replace route permission, CSRF, confirmation, record ownership, audit or transaction checks. Those remain mandatory integration responsibilities before any mutating action is exposed.

This phase adds contracts and tests only. Pages continues to expose only the client-side `export.selected` action.
