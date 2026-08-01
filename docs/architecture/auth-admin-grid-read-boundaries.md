# Auth admin Grid read boundaries

Phase 4M adds feature-owned, storage-neutral read boundaries for Admin Users and
Roles. Generic Grid criteria are converted into small typed criteria objects before
they reach persistence.

The phase does not alter the existing write repositories, password handling,
role-assignment transactions, permissions, CSRF or 2FA flows.

Concrete PDO adapters are intentionally deferred until the current table and column
contracts are verified. The next phase can implement allow-listed sorting and bound
filters behind these interfaces without changing the Grid or controller contracts.
