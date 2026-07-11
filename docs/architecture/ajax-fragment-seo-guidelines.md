# AJAX fragment and SEO guidelines

## Server-render in initial HTML

Keep the following server-rendered for SEO and stable first paint:

```text
page title
H1
main CMS content
canonical URL
hreflang
robots meta
structured data
primary navigation where practical
breadcrumbs
primary image markup
```

## AJAX-load later

Use AJAX fragments for:

```text
cart count
wishlist count
customer header state
admin notification counters
recent activity widgets
dashboard metrics
personalised recommendations
```

## Cache policy

- Public non-personal fragments: short `public` max-age.
- Private user-specific fragments: `private, no-cache` or `no-store`.
- Sensitive security flows: `no-store`.

## Performance goal

The first HTML response should stay fast, cacheable and SEO-complete. Dynamic fragments can load after initial render to reduce server work and improve perceived performance.
