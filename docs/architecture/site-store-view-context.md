# Phase 0.52 - Site/store-view context foundation

## Selected roadmap direction

This phase extends the CDN foundation before moving to WYSIWYG. The goal is to avoid hard-coding store codes in templates, editors, media URLs or feature code.

## Why now

CDN URL generation needs to know the current dynamic website/store-view URL. WYSIWYG media and page links will also need the correct context. Adding context now prevents rework later.

## Design

```text
HTTP request host/path
        ↓
SiteContextResolver
        ↓
SiteContext
        ↓
CdnUrlResolver::dynamicForContext()
```

## Components

```text
config/sites.php
Zoosper\Core\Site\SiteContext
Zoosper\Core\Site\SiteContextResolver
Zoosper\Core\Site\CurrentSiteContext
Zoosper\Core\Site\SiteContextResolverFactory
CdnUrlResolver::dynamicForContext()
```

## DB changes

None in this phase. Configuration is file/env based first. A future phase can move website/store/store-view definitions to database tables with admin UI.

## API/contracts impacted

New internal site context contract:

```php
$context = $siteContextResolver->resolve($host, $path);
$cdn->dynamicForContext('/about-us', $context);
```

Existing CDN resolver methods remain backward compatible.
