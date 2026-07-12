# Phase 1.11.1 progress report

## Feature name

Admin User Locale Persistence Hotfix.

## Implemented

- Updated persistence patch to avoid relying on a `$submitted` array insertion point.
- Passes normalised `$_POST['locale']` directly into `AdminUser` construction.
- Patches repository insert/update SQL and parameter binding for `locale`.
