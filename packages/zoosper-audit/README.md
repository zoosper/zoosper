# Zoosper Audit Package

Admin activity and login history auditing for Zoosper CMS.

## Responsibilities

This package is responsible for:
- Providing the `AuditLoggerInterface` and `LoginHistoryRecorderInterface` contracts.
- Implementing the `AuditLogger` service for recording admin activities.
- Persisting audit logs and login history to the database via repositories.
- Providing Admin Grid definitions for viewing audit trails and login history.
- Redacting sensitive metadata keys before audit records are stored.
- Exposing the audit trail to the rest of the application via decoupled interfaces.

## Architecture

The Audit package provides high-level logging services that other modules can consume without knowing the underlying persistence details.

## Usage

Other modules should type-hint against `Zoosper\Audit\Contract\AuditLoggerInterface` to record actions. The implementation is automatically registered in the service container.

## Dependencies

This package depends on:
- `zoosper/errors` (for sensitive-key redaction)
- `zoosper/grid` (for admin view definitions)
- `zoosper/pagination` (for result listing)

## Redaction

Audit metadata is recursively redacted by key using the `zoosper/errors` sensitive-value policy before storage, including nested JSON objects. This is not content inspection: callers must not put secrets in summaries, other fields, or metadata under non-sensitive keys. Existing records are not retroactively redacted.

## Testing

The Audit package includes unit tests for its repositories and logging services.

- Full repository suite: `zcomposer test`.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

Audit logs can grow quickly in busy systems. Use `admin:logs:prune --days=90` periodically to remove old entries; `--days` must be a positive integer and defaults to 90 when omitted.
