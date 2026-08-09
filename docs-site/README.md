# Zoosper documentation website

This zero-dependency static wrapper builds `docs.zoosper.com` directly from canonical Markdown in `../docs`.

```bash
php docs-site/build.php
php -S 127.0.0.1:8080 -t docs-site/build
```

Generated output lives in `docs-site/build/` and is not committed.
