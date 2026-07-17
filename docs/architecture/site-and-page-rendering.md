# Site and Page Rendering Architecture

Zoosper renders CMS pages through the request-carried site context and the selected frontend theme.

## Request flow

```text
HTTP request
  -> Request::fromGlobals()
  -> Application resolves SiteContext once
  -> Request::withSiteContext(SiteContext)
  -> Router fallback
  -> PageController
  -> PageRepository by site_id + slug
  -> PageRenderer
  -> TemplateRenderer / theme override
  -> HTML response
```

## Important rules

- A site context is resolved once and carried on `Request::siteContext()`.
- Pages are scoped by `site_id`.
- Only pages with `status = published` are rendered publicly.
- Page titles and slugs remain escaped in templates.
- Page body HTML is prepared by `PageRenderer` and rendered without double escaping.
- Existing HTML pages render `pages.content`.
- `block_json` pages render supported `content_json` blocks through `BlockJsonToHtmlRenderer` with HTML fallback.
- The homepage uses the site's `homepage_slug` when the request path is `/`.

## Tables

- `sites`
- `site_domains`
- `pages`
- `page_revisions`

## API endpoint

```text
GET /api/v1/content/page?slug=home
```
