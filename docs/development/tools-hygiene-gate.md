# Tools Hygiene Gate

## Purpose

The hygiene check keeps the `tools/` directory lean by flagging common sources
of drift automatically, so leftover one-off helpers and dead files are caught
instead of quietly accumulating (the kind of file bloat we deliberately avoid).

## What it flags

| Rule | Example | Action |
| --- | --- | --- |
| Leftover one-off helper | `tools/cleanup-legacy-*.php` still present | Delete after use |
| Empty / near-empty tool file | 0 bytes or only `<?php` | Remove or implement |
| Versioned duplicate | `foo-v2.php` alongside `foo.php` | Consolidate into one |

All findings are **warnings** by default and do not fail the build.

## Running

```bash
php8.5 tools/gate.php            # advisory warnings only
php8.5 tools/gate.php --strict   # warnings promoted to build-failing errors
echo "exit code: $?"
```

- Standard mode: hygiene informs without blocking your normal deploy cadence.
- Strict mode (recommended for CI): guarantees a clean tools/ directory.

## Keeping tools/ lean

- Prefer subcommands on an existing CLI over new standalone scripts.
- Delete one-off `cleanup-*/apply-*/fix-*` helpers as soon as they have run.
- Never leave `*-v2` files beside their base once the merge is done.
