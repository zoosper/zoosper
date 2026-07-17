# Render Context Wiring

## Goal

Make site context, CDN URL resolution and cache-key helpers available to page/theme rendering without hard-coding store codes.

## Current model

Render context is explicit. Frontend requests carry the resolved site context on `Request::siteContext()`. Non-request renders, such as previews or diagnostics, pass an explicit `SiteContext` derived from the known site or from `SiteContextResolver::resolve($host, $path)`.

## Components wired

```text
SiteContextResolver
SiteContext
CdnUrlResolver
CacheKeyBuilder
TemplateViewContextProvider
TemplateRenderer
PageRenderer
```

## Data exposed to templates

```php
$siteContext
$cdn
$cacheContext
$cacheKeys
```

These are safe public metadata/helper objects only. They must not contain credentials, session IDs, CSRF tokens, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data or customer-private values.

## What this does not do

- It does not enable full-page cache storage.
- It does not enable block cache storage.
- It does not add CDN purge integration.
- It does not change CMS content to AJAX.
- It does not add WYSIWYG.

## Why TemplateViewContextProvider exists

It keeps shared render context generation in one small provider while still requiring callers to pass the request-carried or otherwise explicit `SiteContext`.
