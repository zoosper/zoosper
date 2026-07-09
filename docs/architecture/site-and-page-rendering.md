# Site and Page Rendering Architecture

Phase 0.3 introduces the first real CMS flow.

## Request flow

```text
HTTP request
  -> Router
  -> PageController fallback
  -> SiteResolver by host
  -> PageRepository by site_id + slug
  -> PageRenderer
  -> HTML response
```

## Important rules

- A site is resolved from the request host.
- Pages are scoped by `site_id`.
- Only pages with `status = published` are rendered publicly.
- User-authored page content is escaped before rendering.
- The homepage uses the site's `homepage_slug` when the request path is `/`.

## Tables

- `sites`
- `site_domains`
- `pages`
- `page_revisions`

## CLI examples

```bash
php bin/zoosper site:create --code=main --name='Main Website' --host=127.0.0.1
php bin/zoosper page:create --site=main --title='Home' --slug=home --content='Welcome to Zoosper.'
```

## API endpoint

```text
GET /api/v1/content/page?slug=home
```
