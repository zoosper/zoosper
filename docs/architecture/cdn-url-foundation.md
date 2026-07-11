# Phase 0.51 - CDN URL foundation

## Selected roadmap item

**CDN Integration** was selected because it is the first roadmap item and it is foundational for later Media Library, WYSIWYG editor, SEO, theme asset and performance work.

## Architecture

Phase 0.51 introduces a small URL resolver layer with three explicit URL channels:

```text
dynamic  -> store-view aware links
media    -> uploaded images/videos/files
static   -> CSS/JS/JSON/theme/module assets
```

## Components

```text
config/cdn.php
Zoosper\Core\Url\CdnUrlType
Zoosper\Core\Url\CdnUrlResolver
Zoosper\Core\Url\CdnUrlResolverFactory
```

## Data flow

```text
.env/config -> ConfigRepository -> CdnUrlResolverFactory -> CdnUrlResolver -> dynamic/media/static URL
```

## DB changes

None.

## Contracts impacted

New service contract by convention:

```php
$resolver->dynamic('/page', 'default');
$resolver->media('/image.jpg');
$resolver->staticAsset('/admin/app.css');
```

No existing public controller/API route is changed in this phase.

## Security

CDN URLs must never include credentials, signed secrets, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, payment data or customer-private values.
