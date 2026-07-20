# Media package documentation operations

Run the package docs audit from the project root:

```bash
php8.5 tools/audit-doc-package-ownership.php
```

Generate a candidate migration plan:

```bash
php8.5 tools/plan-package-docs-migration.php
```

The plan writes:

```text
package-docs-migration-plan.txt
```

Do not commit that generated plan unless it is intentionally promoted to durable documentation.
