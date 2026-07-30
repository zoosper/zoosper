# Zoosper Errors

`zoosper/errors` is Zoosper's loud-errors exception, sensitive-value
redaction, and CLI formatting package — extracted from `zoosper-core` on
2026-07-30 as the first module moved into standalone-package form under
this project's ongoing module-extraction effort.

## Why this was the first module extracted

Exception/error-handling code is a near-leaf dependency: it depends on
almost nothing else in the Zoosper ecosystem, but almost everything else
depends on it. This package's own `composer.json` requires only
`marko/core` — not `zoosper/core`, not any other Zoosper module — which is
itself proof of how cleanly this extracts.

## What this module owns

```text
- ZoosperException (extends Marko\Core\Exceptions\MarkoException — see below)
- SensitiveValueRedactor (redacts credentials/tokens/secrets before display or logging)
- ConsoleExceptionFormatter (CLI-friendly exception formatting with suggestions)
- Co-located Pest unit tests
```

## Real Marko framework integration

`ZoosperException` formally `extends Marko\Core\Exceptions\MarkoException`
(from the already-installed `marko/core` package) rather than being a
parallel, hand-built implementation of the same message/context/suggestion
concept. This means:

- Every Zoosper exception is automatically recognised by Marko's own
  error-reporting pipeline (`Marko\Errors\ErrorReport::fromThrowable()`,
  from the `marko/errors` package) via `instanceof MarkoException` — with
  zero glue code, and this benefit continues automatically as Marko's
  ecosystem grows.
- `ZoosperException` still adds two Zoosper-specific fields Marko's own
  base class does not have: `docsUrl` and `details` (a structured,
  redacted array of diagnostic context).

## Module registration

The package advertises the module through Composer metadata:

```json
{
  "type": "zoosper-module",
  "extra": {
    "zoosper": {
      "module": "module.php",
      "name": "Zoosper_Errors"
    }
  }
}
```

## Development inside the root project

From the Zoosper root project:

```bash
PHP=php8.5 composer dump-autoload
vendor/bin/pest packages/zoosper-errors/tests/Unit
```

## Standalone repository readiness

When this package is moved to its own repository, it should keep:

```text
composer.json
module.php
src/
tests/
phpunit.xml.dist
README.md
```

This package depends on `marko/core` only — no other Zoosper module —
making it genuinely installable standalone today, without any path-repository
workaround.
