# Rate Limit Runtime Config and Reporting

Phase 1.39ae-ai adds the runtime configuration and report sink needed before wiring report-only rate limiting into the real middleware pipeline.

## Why this phase exists

The middleware integration readiness audit found the existing report-only middleware adapter and guard files, and it scanned the repository for pipeline candidates. Because the audit reported many candidate files, the next safe step is to add the runtime config and report sink foundation before mutating the real middleware list.

## Runtime config

The new config file is:

```text
app/zoosper-core/config/rate_limit.php
```

Default values are intentionally safe:

```php
'enabled' => false,
'mode' => 'report_only',
'policies' => [],
```

This means no route is limited by default.

## Runtime config object

```text
Zoosper\Core\Security\RateLimit\RateLimitRuntimeConfig
```

The config object converts array config into explicit runtime values and `RateLimitRule` objects.

## Report sink

```text
Zoosper\Core\Security\RateLimit\FileRateLimitReportSink
```

The file sink writes one JSON object per report event. It is intended for report-only diagnostics and smoke testing before enforcement.

## Next phase

The next phase can wire report-only middleware behind this disabled-by-default config. The pipeline should only instantiate or execute report-only rate limiting if the config explicitly enables it.

## Safety requirements

- Default config remains disabled.
- Default policy list remains empty.
- Report-only mode never blocks requests.
- File reports must write opaque identity hashes, not raw identities.
- Enforcement must remain a separate future phase.
