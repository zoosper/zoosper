# Zoosper Audit Package

Admin activity and login history auditing for Zoosper CMS.

## Responsibilities

This package is responsible for:
- Providing the `AuditLoggerInterface` and `LoginHistoryRecorderInterface` contracts.
- Implementing the `AuditLogger` service for recording admin activities.
- Persisting audit logs and login history to the database via repositories.
- Providing Admin Grid definitions for viewing audit trails and login history.
- Ensuring that sensitive data (passwords, tokens) is redacted from audit logs.
- Exposing the audit trail to the rest of the application via decoupled interfaces.

## Architecture

The Audit package is designed as a standalone module that depends only on Core and Database. It provides high-level logging services that other modules can consume without knowing the underlying persistence details.

## Usage

Other modules should type-hint against `Zoosper\Audit\Contract\AuditLoggerInterface` to record actions. The implementation is automatically registered in the service container.

## Dependencies

This package depends on:
- `zoosper/grid` (for admin view definitions)
- `zoosper/pagination` (for result listing)

## Redaction

The `AuditLogger` uses a redaction policy to ensure that sensitive values like `password`, `token`, and `secret` are never stored in the audit database. This matches the security standards of the broader Zoosper CMS project.

## Testing

The Audit package includes unit tests for its repositories and logging services.

- Full repository suite: `zcomposer test`.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

Audit logs can grow quickly in busy systems. It is recommended to use the `admin:prune-logs` command periodically to remove old entries and maintain database performance.
