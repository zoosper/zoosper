# Phase 0.54 - Render context wiring

## Goal

Make site context, CDN URL resolution and cache-key helpers available to actual page/theme rendering without hard-coding store codes.

## Components wired

```text
SiteContextResolver
CurrentSiteContext
CdnUrlResolver
CacheKeyBuilder
TemplateViewContextProvider
TemplateRenderer
PageRenderer
```

## Data exposed to templates

```php
$siteContext
$currentSiteContext
$cdn
$cacheContext
$cacheKeys
```

These are safe public metadata/helper objects only. They must not contain credentials, session IDs, CSRF tokens, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data or customer-private values.

## What this phase does not do

- It does not enable full-page cache storage.
- It does not enable block cache storage.
- It does not add CDN purge integration.
- It does not change CMS content to AJAX.
- It does not add WYSIWYG.

## Why TemplateViewContextProvider exists

It keeps the shared render context in one small provider instead of making every controller inject and pass site/CDN/cache helper data manually.
