# Sites/Domains Implementation Target Inspection

Run the source-only inspection:

```bash
php8.5 tools/inspect-sites-domains-implementation-targets.php
```

This writes:

```text
sites-domains-implementation-targets.txt
```

Review it locally to identify:

```text
- route config format
- controller factory format
- admin layout/rendering convention
- existing SiteRepository methods
- site schema shape
- whether a SiteDomainRepository already exists
```

Do not commit the generated inspection output by default:

```bash
rm -f sites-domains-implementation-targets.txt
```

Run the blueprint audit:

```bash
php8.5 tools/audit-sites-domains-implementation-blueprint.php
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminImplementationBlueprintTest.php
```
