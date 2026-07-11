# Phase 0.53 - Cache context and AJAX fragment strategy foundation

## Selected phase

This phase was selected after CDN and site/store-view context foundations because caching must vary by the current site/store-view context before renderers, WYSIWYG, media and block rendering start using shared cache.

## Why it matters

A page or block cache key that only uses the path can serve the wrong website, locale, currency, menu or content after multi-site/store-view support is enabled. This phase creates cache context and key-building contracts before a full cache manager is implemented.

## Architecture

```text
HTTP request
  -> SiteContextResolver
  -> CacheContext
  -> CacheKeyBuilder
  -> page/block/fragment cache key
```

## Components

```text
Zoosper\Core\Cache\CacheContext
Zoosper\Core\Cache\CacheKeyBuilder
Zoosper\Core\Cache\HttpCachePolicy
Zoosper\Core\Fragment\AjaxFragmentDefinition
Zoosper\Core\Fragment\FragmentResponseMetadata
```

## Cache dimensions

Public cache keys include:

```text
website
store
store_view
locale
currency
theme
host
path
route
```

Private fragment keys may additionally include coarse safe dimensions:

```text
auth
customer_group
```

Do not include raw session IDs, CSRF tokens or private customer data in shared cache keys.

## AJAX strategy

Server-render SEO-critical content in the initial HTML. Use AJAX fragments for non-critical or personalised content such as cart counts, customer header state, dashboard widgets or notification counters.
