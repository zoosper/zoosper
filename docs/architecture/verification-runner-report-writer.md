# Phase 0.93 - Verification Runner / Report Writer

Zoosper now has a single verification runner script that executes the growing verifier suite and writes the full output to a report file.

## Runner

```text
tools/run-verification-suite.php
```

## Default output

```text
var/reports/verification-YYYYMMDD-HHMMSS.txt
```

## Custom output

```bash
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

The terminal output stays compact so the full report does not need to be copied from scrollback.
