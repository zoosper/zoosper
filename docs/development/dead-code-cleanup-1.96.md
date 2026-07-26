# Dead Code Cleanup — Phase 1.96

## What this removes and why

Three independent signals agreed these are dead:
- Psalm `--find-unused-code` (UnusedClass).
- Both external reviews (duplicate momentum stack; RoleAdmin still on .php views).
- The repository find-scan for unreferenced view files.

### A. Duplicate Page Momentum stack (unwired)
Built in phases 1.86-1.89 as a second implementation, but the routed dashboard
lives in `zoosper-page` (`PageMomentumDashboardFactsProvider`). The
`Zoosper\Admin\PageMomentum\*` classes were never registered in any
`config/services.php` or `config/controllers.php`, so nothing constructs them.

### B/C. Dead views
The LIVE roles views are `app/zoosper-admin/resources/views/admin/roles/*.php`
(loaded by `RoleAdminController::renderRoleView()`). The `.latte` roles views in
`zoosper-core/views` and the older `zoosper-auth` roles `.php` views are
unreferenced, as are several users/page views from the find-scan.

## Safety model

The `tools/verify-and-clean-dead-code.php` tool re-verifies, per target, that
there are zero references anywhere in `app/` outside the file itself and outside
`tests/`. Only zero-live-reference targets are deleted (and only with `--apply`).
References that exist ONLY in tests are `assertFileExists`-style pins; those test
files are listed for deletion in the same commit because they assert that a
process artifact exists rather than testing behaviour (a pattern both reviews
flagged).

## After deletion

- Re-run Psalm: the corresponding UnusedClass entries for
  `Zoosper\Admin\PageMomentum\*` should be gone.
- Re-run `vendor/bin/pest` and `php tools/gate.php --strict`.
- The asset-pipeline classes (AssetController, AssetUrlGenerator,
  ModuleAssetManifestLoader) are intentionally NOT in scope here: they are unused
  only because the `/asset/{module}/{path}` route is not wired yet (a pending
  phase), not because they are dead.
