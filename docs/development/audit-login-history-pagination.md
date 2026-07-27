# Audit Log & Login History Pagination + Retention

## Problem

Both views showed only the newest 100 rows with no pagination, filtering, or
retention, and both tables grow unbounded with no cleanup mechanism.

## Fix

Mirrors the existing, proven PageGridRepository/PageGridCriteria pattern:

- `AuditLogCriteria` / `LoginHistoryCriteria` — Pager + filter fields, built
  from request query params via `fromQuery()`.
- `AuditLogRepository::paginate()` / `LoginHistoryRepository::paginate()` — a
  COUNT query plus a filtered, bound LIMIT/OFFSET query, returning the shared
  `PaginationResult`.
- `deleteOlderThan(string $cutoff): int` on both repositories — the retention
  primitive; a CLI wrapper is a follow-up (needs `tools/bootstrap.php`).

`latest()` is unchanged on both repositories for backward compatibility.

## Controllers

Both `index()` methods now paginate by default (page 1, 20 rows) instead of
returning the top 100 unconditionally. The `rows` view-data key is preserved;
`pagination`/`criteria`/`linkParameters` are additive.
