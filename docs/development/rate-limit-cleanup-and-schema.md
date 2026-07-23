# Rate Limit Cleanup and Schema Registration

Phase 1.39j-l connects the database-backed rate-limit store to long-term operational hygiene.

## Scope

This phase adds:

- schema-engine registration for `rate_limit_buckets`;
- a cleanup command for expired buckets;
- an audit tool and regression tests.

It intentionally does not wire request middleware enforcement yet.

## Schema registration

The `rate_limit_buckets` table should be registered in:

```text
app/zoosper-core/config/db_schema.php
```

Expected logical fields:

```text
id
scope
identity_hash
rule_key
window_starts_at
window_ends_at
attempts
created_at
updated_at
```

Expected indexes:

```text
unique: scope, identity_hash, rule_key, window_starts_at
index: window_ends_at
```

## Cleanup command

```text
tools/cleanup-expired-rate-limit-buckets.php
```

Default mode is dry-run. Destructive cleanup requires `--apply`.

Supported options:

```text
--database=<sqlite path>
--now=<unix timestamp>
--apply
--output-dir=<path>
```

## Safety

- Cleanup only deletes buckets with `window_ends_at <= now`.
- Dry-run should be safe for production diagnostics.
- The command writes a report under `var/reports`.
- The command should not know about raw request identities; it only handles bucket rows.

## Next phase

After schema and cleanup tooling pass, the next implementation phase should introduce policy and middleware seams without enforcing route policies globally yet.
