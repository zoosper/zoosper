# Phase 0.92 progress report

## Feature name

Admin/Site Locale Resolution Foundation.

## Implemented

- Added `site_locale` to `config/i18n.php`.
- Added `LocaleResolution` value object.
- Added `LocaleResolverInterface`.
- Added `ConfiguredLocaleResolver`.
- Added verifier for admin/site locale resolution.

## Why

Phase 0.91 made catalogue-backed admin translation possible. Phase 0.92 provides the reusable locale resolution contract needed before per-admin and per-site locale selection are wired into runtime services.
