## Phase 1.67 cleanup guard scope fix

Status: ready to apply

Fixes Page Momentum cleanup closure and lean hygiene audits so strict mode fails only on real stale/obsolete references, not on approved cleanup vocabulary or current durable dashboard tests.

Safety:

- Read-only audit changes only.
- No runtime source changes.
- No file movement/deletion.
