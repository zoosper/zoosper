# Reviewer backlog reconciliation

The shared Sonnet and Fable reviews remain useful as historical audit input, but
many findings are already resolved by later phases. Completed work includes
module collision failures, Composer-authoritative identity and versions,
canonical package homes, bounded internal constraints, package webroot
hardening, schema CLI error handling, declarative-only audit-table ownership,
LONGTEXT mail bodies, environment consolidation, atomic module-manifest writes,
repository-tool cleanup, Grid extraction, Admin Grid extraction, API-first
view-engine contracts, and Page migration to the shared Grid.

The remaining findings should be re-verified against current HEAD before being
scheduled. Highest-value candidates are:

1. deploy-order/cache-staleness simulation and a versioned manifest stamp;
2. dead duplicate subsystem removal, especially 2FA, Site resolver,
   translation, editor config and asset rendering;
3. HTTP lazy-PDO and early error-handler parity with the CLI;
4. optional bare-Core boot without Site or API modules;
5. pairwise feature-module dependency/import enforcement;
6. Site and Site Domain pagination on the shared Grid;
7. compiled discovery caches beyond the module manifest;
8. Page store-view assignment, either fully wired or intentionally removed;
9. Marko dependency usage audit and removal/adoption decisions;
10. module lifecycle and uninstall design.

No old-review item should be treated as currently live solely because it appears
in the review. Each must have a current source-path reproduction or regression
test before implementation.
