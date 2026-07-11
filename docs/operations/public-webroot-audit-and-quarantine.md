# Public webroot audit and quarantine

Audit:

```bash
php tools/audit-public-webroot.php
```

Verify policy files:

```bash
php tools/verify-public-webroot-hardening.php
```

Quarantine suspicious files only after review:

```bash
php tools/quarantine-public-webroot-files.php --yes
```

Quarantine files are moved to `var/quarantine/public-webroot/...`, never under `public/`.

Nginx hardening sample:

```text
deploy/nginx/zoosper-public-hardening.conf
```

Include it inside your HTTPS server block before the generic front-controller fallback and run:

```bash
sudo nginx -t
sudo systemctl reload nginx
```
