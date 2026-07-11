# Phase 0.66 - Public webroot hardening

Zoosper treats `public/` as a strict webroot. Runtime files, caches, logs, exports, quarantine files and private uploads must live outside `public/`.

## Allowed public surface

```text
public/index.php
public/static/
public/assets/
public/media/ later, after media validation exists
```

## Blocked examples

```text
public/var/
public/storage/
public/vendor/
public/app/
public/config/
public/modules/
public/tools/
```

Executable/server-side files are blocked except for the controlled front controller.
