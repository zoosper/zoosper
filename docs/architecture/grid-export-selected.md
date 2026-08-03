# Export selected bulk action

`Export selected` is the first executable shared bulk action. It exports only rows explicitly selected on the current rendered page. The CSV is generated locally from the visible table headers and cells, excluding the selection-checkbox column. Values are quoted and embedded quotes are escaped; a UTF-8 BOM supports spreadsheet applications.

This action is deliberately read-only and does not call a feature module or remote API. It therefore validates the shared selection interaction without introducing mutation, permission, CSRF, audit or partial-failure semantics. Server-side and cross-page actions remain separate future contracts.
