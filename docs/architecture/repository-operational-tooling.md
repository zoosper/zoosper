# Repository operational tooling

Zoosper keeps scripts only when they serve an ongoing development, CI,
deployment, diagnostic or maintenance workflow.

Phase 2C retires:

- `collect-and-run.sh`, an interactive session helper that accepted filenames
  and shell commands from standard input;
- `bin/cleanup-legacy-tooling.sh`, a completed one-shot cleanup utility;
- `bin/cleanup-old-root-tests.sh`, another completed one-shot cleanup utility;
- `bin/pest.sh`, an unused duplicate test runner.

Composer scripts remain the canonical test interface:

```text
composer test
composer test:unit
composer test:feature
composer test:coverage
```

Historical repair tooling belongs in version-control history once its job is
complete. New scripts should have a durable owner, documented invocation and a
current workflow or regression test that justifies keeping them.
