# Zoosper documentation website

This zero-dependency static wrapper builds `docs.zoosper.com` directly from canonical Markdown in `../docs`.

```bash
php8.5 docs-site/build.php
php8.5 -S 127.0.0.1:8080 -t docs-site/build
```

The build consumes only the durable pages listed in `build.php` and fails if a listed source or generated internal link is missing. Generated output lives in `docs-site/build/` and is not committed.

## Automated publishing

Pushes to `dev` that change canonical documentation, the site builder, or the publishing workflow build the site and deploy it through the official GitHub Pages artifact pipeline. `docs-site/CNAME` is the source-controlled custom-domain declaration; the build emits both `CNAME` and `.nojekyll`. Generated output remains ignored by the parent repository and is never committed by the workflow.

The local nested repository under `docs-site/build/.git`, when present, is legacy operator metadata only. The automated workflow does not read, modify, commit, or push that repository.
