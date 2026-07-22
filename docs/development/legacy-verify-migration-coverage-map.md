# Legacy Verify Migration Coverage Map

This document maps the first legacy verification migration pilot batch to expected Pest ownership.

The mapping is intentionally explicit so legacy `tools/verify-*` scripts are migrated by preserving intent, not by deleting files to reduce count.

## Pilot batch coverage map

| Legacy verify script | Expected Pest ownership | Migration status |
|---|---|---|
| `tools/verify-project-structure.php` | `app/zoosper-core/tests/Unit/Tools` or existing project-structure tests | Planned |
| `tools/verify-runtime-path-safety.php` | runtime path safety tests / public webroot policy tests | Planned |
| `tools/verify-service-provider-manifest-file.php` | service provider manifest/config tests | Planned |
| `tools/verify-module-composer-manifests.php` | module composer manifest/package identity tests | Planned |
| `tools/verify-roadmap-planning-docs.php` | documentation/roadmap tests | Planned |

## Removal gate

A script can move from `Planned` to `Migrated` only when:

1. Equivalent Pest coverage exists.
2. Full Pest suite passes.
3. Tools inventory report is regenerated.
4. The migrated script is removed in a focused commit.
5. Generated `var/reports` artefacts remain uncommitted unless intentionally promoted.

## Next deletion candidate

The safest first deletion candidate is usually the smallest source-only script in this map after its source has been inspected and equivalent Pest assertions are already present.
