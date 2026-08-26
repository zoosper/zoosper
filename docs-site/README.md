# Zoosper documentation website

This zero-dependency static wrapper builds `docs.zoosper.com` directly from canonical Markdown in `../docs`.

```bash
php8.5 docs-site/build.php
php8.5 -S 127.0.0.1:8080 -t docs-site/build
```

The build consumes only the durable pages listed in `build.php` and fails if a listed source or generated internal link is missing. Generated output lives in `docs-site/build/` and is not committed.
