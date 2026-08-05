# Module manifest runtime diagnostics

Phase 8D makes compiled-manifest fallback observable without changing discovery behaviour.

`ModuleRegistry::compiledManifestRejectionReason()` returns `null` when no present manifest was rejected. After rejection it exposes one stable machine-readable reason:

- `manifest-unreadable`
- `freshness-stamps-missing`
- `composer-lock-changed`
- `first-party-modules-changed`
- `manifest-load-failed`
- `manifest-shape-invalid`
- `manifest-entry-invalid`

The registry still fails safely to live discovery. A missing optional compiled manifest is normal and is not recorded as a rejection.
