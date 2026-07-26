# Site Lookup: Read/Write Boundary

- `Zoosper\Core\Site\SiteLookupInterface` is a core-owned, read-only request-resolution seam and returns `ResolvedSite`.
- Admin page/theme flows need the full `Zoosper\Site\Model\Site` and write operations such as `updateTheme`, so they should use `Zoosper\Site\Repository\SiteRepository`.

Do not add write methods such as `updateTheme()` to `SiteLookupInterface`.
