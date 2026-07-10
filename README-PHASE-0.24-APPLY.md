# Apply Phase 0.24 URL Rewrites and Dynamic Admin Path Foundation

Apply from repository root:

```bash
unzip zoosper-phase-0.24-url-rewrites-dynamic-admin-path-foundation.zip -d /tmp/zoosper-phase-0.24
cp -R /tmp/zoosper-phase-0.24/zoosper-phase-0.24-url-rewrites-dynamic-admin-path-foundation/* .
composer dump-autoload
```

Smoke test:

```bash
php -l config/admin.php
php -l config/url_rewrite.php
php -l app/zoosper-admin/src/Routing/AdminPathResolver.php
php -l app/zoosper-admin/src/Routing/AdminUrlGenerator.php
php -l app/zoosper-url-rewrite/src/Model/UrlRewrite.php
php -l app/zoosper-url-rewrite/src/Repository/UrlRewriteRepository.php
php -l app/zoosper-url-rewrite/src/Service/UrlRewriteResolver.php
```

Migration note:

```bash
php bin/zoosper migrate
```

If the migration runner does not auto-detect raw SQL files yet, execute:

```sql
source database/migrations/20260710002400_create_url_rewrites_table.sql;
```

This phase is mostly additive. To fully activate the dynamic admin path, the route loader, admin menu builder and admin templates should later be updated to use `AdminUrlGenerator` instead of hard-coded `/admin` strings. Ask for the latest code before replacing those existing files.
