# zoosper/logger

Zoosper-owned multi-channel logging boundary backed by the real Marko file logger.

## Responsibilities

- Own the native `Zoosper\Logger\Contract\LoggerInterface` used by Core and feature consumers.
- Resolve default, exception, explicit-file, and module-owned logger channels.
- Discover enabled-module `config/logging.php` contributions and register `logger.<module-name>` services.
- Redact sensitive context recursively before delegating writes.
- Preserve legacy Zoosper log filenames while Marko `DailyRotation` owns authoritative dated files.
- Keep concrete Marko logging classes out of `zoosper/core` and other consumers.

## Architecture

- `src/Contract/LoggerInterface.php` defines the native application boundary.
- `src/Manager/LogManager.php` resolves channels from the existing `logging.*` configuration.
- `src/Driver/LocalLogger.php` adapts the native boundary to Marko `FileLogger` and maintains safe legacy links.
- `src/Module/ModuleLoggerProviderLoader.php` discovers module logging contributions.
- `module.php` is the empty discovery marker required by the module system.

Each resolved channel receives its own real Marko `FileLogger`. This avoids collapsing every module into the single global `log.channel` setting. Rotation strategy selection is deliberately not configurable in this phase; `DailyRotation` is explicit.

## Configuration

Existing Zoosper keys remain authoritative: `logging.enabled`, `logging.path`, `logging.level`, `logging.default_file`, `logging.error_file`, `logging.format`, `logging.date_format`, `logging.escape_newlines`, and `logging.modules.*`.

A feature module contributes a channel through `config/logging.php`, returning an array such as `['file' => 'media.log']`. The contribution remains available as `logger.<module-name>`. Existing logical channel names and configured paths remain stable. Marko writes dated files with `FILE_APPEND | LOCK_EX`.

## Extension points

Consumers should type against `Zoosper\Logger\Contract\LoggerInterface`. Projects may replace the manager or native logger binding without changing Core, Router, EventDispatcher, Media, console, or HTTP consumers. Configurable rotation selection is intentionally deferred to a separately tested phase.

## Dependencies

Production dependencies are:

- PHP `^8.5`
- `marko/log` `0.8.5`
- `marko/log-file` `0.8.5`

The package intentionally has no dependency on `zoosper/core`, preventing a Composer dependency cycle. Root Composer requires `zoosper/logger`; this package owns the concrete Marko dependencies.

## Testing

Run the package suite:

```bash
php8.5 vendor/bin/pest packages/zoosper-logger/tests
```

Run focused ErrorHandler integration tests:

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Log/ErrorHandlerMarkoIntegrationTest.php
```

Run the complete repository suite and standard quality gate:

```bash
zcomposer test
php8.5 tools/gate.php
```

Also run Composer validation, `git diff --check`, module-manifest compilation, freshness checks, and documentation checks before release.

## Operational notes

The configured log directory must be writable by PHP-FPM and console users. Daily files such as `system-YYYY-MM-DD.log` are authoritative. Where the legacy path does not already contain a regular file, a link such as `system.log` points to the current daily file. Existing regular legacy log files are never deleted by the adapter.

Context keys containing `password`, `token`, `secret`, or `session` are redacted recursively. Controlled verification should prove one normal record, one caught or uncaught error record, no duplicate marker, and no leaked sensitive value. Do not remove current dated targets while the application is serving requests.
