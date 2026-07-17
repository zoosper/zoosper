# Site/store-view context foundation

## Selected roadmap direction

The site/store-view context foundation avoids hard-coding store codes in templates, editors, media URLs or feature code.

## Why it exists

CDN URL generation needs to know the current dynamic website/store-view URL. WYSIWYG media and page links also need the correct context.

## Current design

```text
HTTP request host/path
        ↓
SiteContextResolver
        ↓
SiteContext
        ↓
Request::siteContext()
        ↓
CdnUrlResolver::dynamicForContext()
```

Operational tools and non-request render paths resolve context explicitly from host/path instead of using a container-held current-site fallback.

## Components

```text
config/sites.php
Zoosper\Core\Site\SiteContext
Zoosper\Core\Site\SiteContextResolver
Zoosper\Core\Site\SiteContextResolverFactory
CdnUrlResolver::dynamicForContext()
```

## API/contracts impacted

```php
$context = $siteContextResolver->resolve($host, $path);
$cdn->dynamicForContext('/about-us', $context);
```

Existing CDN resolver methods remain backward compatible.
