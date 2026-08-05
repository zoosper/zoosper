# Module manifest health check

Phase 8F adds the strict, read-only `module:manifest:check` command for deployment and monitoring automation.

- Exit code `0`: a compiled manifest exists and is fresh.
- Exit code `1`: the manifest is missing or rejected.
- Failure output includes the cache path, rejection reason when available, and the compile remediation command.

The informational `module:manifest:status` command retains its existing behaviour. The built-in deploy flow verifies the newly compiled manifest before reporting deployment completion.
