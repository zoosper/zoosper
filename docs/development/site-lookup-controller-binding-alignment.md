# Site Lookup Controller Binding Alignment

## What went wrong

Several admin controllers were migrated to depend on the read abstraction `Zoosper\Core\Site\SiteLookupInterface`, but their factories still passed the concrete `SiteRepository`. Some controllers also imported a stale namespace: `Zoosper\Site\Repository\SiteLookupInterface`.

## Rule

- Read-only site consumers should receive `Zoosper\Core\Site\SiteLookupInterface`.
- Write-oriented site admin controllers may keep `SiteRepository`.

## Tool

`tools/align-site-lookup-controller-bindings.php` is dry-run first and surgical:

- fixes only controllers that type-hint `SiteLookupInterface`;
- rewrites the stale import to `Zoosper\Core\Site\SiteLookupInterface`;
- rewrites only the `sites:` factory argument to resolve `\Zoosper\Core\Site\SiteLookupInterface::class`;
- leaves unrelated `SiteRepository` usages untouched.
