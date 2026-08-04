# Shared Grid bulk-action contract

The generic bulk-action vocabulary belongs to `zoosper-grid`. Feature modules register declarative definitions by Grid key and do not implement selection mechanics, shared UI, or registry behaviour.

A definition declares an action ID, label, allowed selection scope, execution boundary, confirmation policy, optional permission, maximum selection and audit requirement. Mutating server and remote actions cannot be declared without confirmation. Client-only downloads cannot claim server audit coverage.

`GridBulkSelection` normalises explicit identities, removes duplicates, rejects empty selections and enforces the definition maximum. This phase adds contracts only. It does not expose new mutating actions or trust browser-selected identities without a future server-side ownership and permission check.
