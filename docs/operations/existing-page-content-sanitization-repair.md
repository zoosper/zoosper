# Existing page content sanitisation repair

Audit current page rows:

```bash
php tools/audit-page-content-sanitization.php --pages --show-samples
```

Audit revisions:

```bash
php tools/audit-page-content-sanitization.php --revisions --show-samples
```

Repair current pages after taking a database backup:

```bash
php tools/repair-page-content-sanitization.php --pages --yes
```

Repair revisions too:

```bash
php tools/repair-page-content-sanitization.php --pages --revisions --yes
```

The repair tool intentionally requires `--yes` and an explicit target.
