# Phase 0.51 progress report

## Feature name

CDN URL foundation.

## Implemented

- Added `config/cdn.php` with separate dynamic/media/static URL channels.
- Added store-view aware dynamic base URL support through JSON config.
- Added `CdnUrlResolver`, `CdnUrlType` and `CdnUrlResolverFactory`.
- Added CDN diagnostics and verification CLI tools.
- Added architecture and operations documentation.

## What remains

- Register resolver in the shared service container once current `ApplicationFactory.php` is available after the latest commit.
- Refactor admin/theme/media/page renderers to consume `CdnUrlResolver` instead of ad hoc URL/path building.
- Add admin settings UI for CDN configuration later if desired.

## Risks / considerations

- CDN misconfiguration can break asset loading. Diagnostics are included to make this visible.
- Media and static asset paths must not include private signed secrets at this stage.
- Route declarations still need a later dynamic admin base-path phase.
