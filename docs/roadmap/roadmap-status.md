# Zoosper CMS - Roadmap Status

**Snapshot date:** 2026-07-15

## Recent phases

| Phase | Title | Status |
|---|---|---|
| 1.20 | Entity Save Lifecycle Events | ✅ Done - foundation classes (dispatcher, context, runner, lifecycle constants, extension persister) |
| 1.21 | Pest harness + first regression suite (co-located) | ✅ Done - 13 tests passing |
| 1.22 | Foundation consolidation / legacy tooling retirement | ✅ Done - 36 dead files removed; `MIGRATE_TO_PEST` = 90 baseline |
| 1.23 | Entity save lifecycle: integration foundation + docs | 🔄 In progress - runner lock-in tests + docs + controller wiring recipe |
| 1.24 | Wire lifecycle into Page + AdminUser controllers (full-file) | ⬜ Planned |
| 1.25 | Unify the two schema engines (keep snapshot-capable) | ⬜ Planned |
| 1.26 | Unify site-resolution into one `SiteContext` | ⬜ Planned |

## Standing rules (carry-forward)

- **Co-located, self-contained module tests** - each module owns `<module>/tests/`;
  no root `tests/` folder.
- **Pest run convention** - root `phpunit.xml`; run from the repo root with
  **no `-c` flag** (Pest v3 mis-parses `--cache-directory`); use **built-in
  expectations only** in co-located module `Pest.php` files.
- **Every behavioural change ships a Pest test.**
- **Coverage never goes down** - retire a `verify-*` script only once its Pest
  replacement exists and passes.
- **Docs updated every phase** - architecture, contributor guides, roadmap,
  reference. A documentation website will be generated from these Markdown files
  with examples, so keep them current.
- **PCI-aware** - never log secrets/tokens/payment data.
- **Clean controllers; extend without touching core.**
- **Full-file drop-ins, not patches.**

## Key metric

- `MIGRATE_TO_PEST` (old `verify-*` scripts remaining) should trend to **0**.
  Track with `php bin/tools-inventory.php`.
