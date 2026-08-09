# Changelog

## 0.2.0-alpha.1-dev

Development line after the `v0.1.0-alpha.1` release. Planned emphasis: useful CMS core, visible Admin and content momentum, richer Media capability, revisions, menus, seed content, starter-theme improvements and continued release-contract hardening.

## 0.1.0-alpha.1

Initial alpha candidate. Includes modular installation and migrations, Admin authentication and two-factor support, site-aware page rendering, Settings workspace, Media foundation, Store Orders workspace, module-owned assets, CLI deployment/recovery commands, CI quality gates and release-readiness diagnostics.

Known limitation: Psalm remains advisory while the existing baseline is reduced. Alpha APIs and extension contracts may change before stable release.

- Added the full Page revision snapshot domain with bounded retention and fresh-install schema coverage.

- Removed the internal Page Momentum launch-readiness dashboard from the production Admin surface.

- Consolidated module and package documentation into concise current READMEs and removed integration patch notes, readiness stubs and historical Media phase documents.

- Removed completed root apply scripts, package-local Media migration/audit tooling, tool-only historical tests and tracked runtime Media artefacts.

- Made `type: zoosper-module` the sole public module identity and centralised private upstream Marko compatibility across Composer and runtime discovery.
